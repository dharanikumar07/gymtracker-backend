<?php

namespace App\Services;

use App\Models\BudgetPlanCycle;
use App\Models\ExpenseLog;
use App\Models\PhysicalActivityTracker;
use App\Models\Plan;
use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsOverviewService
{
    /**
     * Get the analytics overview for a user.
     */
    public function getOverview($user, $params = [])
    {
        $date = $params['date'] ?? now()->toDateString();
        $carbonDate = Carbon::parse($date);
        
        return [
            'expense' => $this->getExpenseMetrics($user, $date, $carbonDate),
            'workout' => $this->getWorkoutMetrics($user, $carbonDate),
            'date_range' => [
                'label' => $carbonDate->format('F Y'),
                'month' => $carbonDate->month,
                'year' => $carbonDate->year
            ]
        ];
    }

    /**
     * Calculate Expense Metrics
     */
    private function getExpenseMetrics($user, $date, $carbonDate)
    {
        $activeCycle = BudgetPlanCycle::where('user_uuid', $user->uuid)
            ->where('cycle_start', '<=', $date)
            ->where('cycle_end', '>=', $date)
            ->first();

        $defaults = [
            'has_plan' => false,
            'plan_details' => [
                'name' => 'No Active Plan',
                'start_date' => null,
                'cycle_period' => 'N/A',
                'budget_amount' => 0
            ],
            'total_spent' => 0,
            'remaining_budget' => 0,
            'percentage_used' => 0,
            'avg_daily_spend' => 0,
            'top_category' => 'None'
        ];

        if (!$activeCycle) {
            return $defaults;
        }

        $plan = Plan::where('uuid', $activeCycle->plan_uuid)->first();

        $totalSpent = (float) ExpenseLog::where('plan_cycle_uuid', $activeCycle->uuid)->sum('amount');
        $budget = (float) $activeCycle->budget_amount;

        // Top Category Logic
        $topCategory = DB::table('expense_logs')
            ->join('expense_categories', 'expense_logs.category_uuid', '=', 'expense_categories.uuid')
            ->where('expense_logs.plan_cycle_uuid', $activeCycle->uuid)
            ->select('expense_categories.category_type', DB::raw('SUM(expense_logs.amount) as total'))
            ->groupBy('expense_categories.category_type')
            ->orderByDesc('total')
            ->first();

        // Daily Burn Calculation
        $start = Carbon::parse($activeCycle->cycle_start);
        $end = Carbon::today()->min(Carbon::parse($activeCycle->cycle_end));
        $daysElapsed = max(1, $start->diffInDays($end) + 1);

        return [
            'has_plan' => true,
            'plan_details' => [
                'name' => $plan->name,
                'start_date' => $activeCycle->cycle_start,
                'cycle_period' => $plan->meta_data['budget_type'] ?? 'Monthly',
                'budget_amount' => $budget
            ],
            'total_spent' => $totalSpent,
            'remaining_budget' => round($budget - $totalSpent, 2),
            'percentage_used' => $budget > 0 ? round(($totalSpent / $budget) * 100, 1) : 0,
            'avg_daily_spend' => round($totalSpent / $daysElapsed, 2),
            'top_category' => $topCategory ? Helper::deslugifyCategory($topCategory->category_type) : 'None'
        ];
    }

    /**
     * Calculate Workout Metrics
     */
    private function getWorkoutMetrics($user, $carbonDate)
    {
        $startOfMonth = $carbonDate->copy()->startOfMonth();
        $endOfMonth = $carbonDate->copy()->endOfMonth();

        $activePlan = Plan::where('user_uuid', $user->uuid)
            ->where('type', 'physical_activity')
            ->where('is_active', true)
            ->first();

        // 1. Skipped Workouts
        $skippedCount = PhysicalActivityTracker::where('user_uuid', $user->uuid)
            ->whereBetween('activity_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'skipped')
            ->count();

        // 2. Most Skipped Week
        $mostSkippedWeek = DB::table('physical_activity_tracker')
            ->where('user_uuid', $user->uuid)
            ->where('status', 'skipped')
            ->whereBetween('activity_date', [$startOfMonth, $endOfMonth])
            ->select(DB::raw("date_trunc('week', activity_date) as week_start"), DB::raw('count(*) as count'))
            ->groupBy('week_start')
            ->orderByDesc('count')
            ->first();

        $skippedWeekLabel = 'None';
        if ($mostSkippedWeek) {
            $ws = Carbon::parse($mostSkippedWeek->week_start);
            $we = $ws->copy()->endOfWeek();
            $skippedWeekLabel = $ws->format('M d') . ' - ' . $we->format('M d');
        }

        // 3. Discipline Rank Calculation
        $completedLogs = PhysicalActivityTracker::where('user_uuid', $user->uuid)
            ->whereBetween('activity_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->get();

        $totalCompleted = $completedLogs->count();
        $totalSets = $completedLogs->sum(function($log) {
            return count($log->metrics_data['sets'] ?? []);
        });

        $score = ($totalCompleted * 10) + ($totalSets * 1);
        $rank = $this->resolveRank($score);

        return [
            'has_plan' => (bool)$activePlan,
            'plan_details' => $activePlan ? [
                'name' => $activePlan->name,
                'start_date' => $activePlan->start_date,
                'end_date' => $activePlan->end_date,
                'pa_type' => Helper::deslugifyCategory($activePlan->meta_data['physical_activity_type'] ?? 'Standard')
            ] : [
                'name' => 'No Active Plan',
                'start_date' => null,
                'end_date' => null,
                'pa_type' => 'N/A'
            ],
            'skipped_count' => $skippedCount,
            'most_skipped_week' => $skippedWeekLabel,
            'discipline_rank' => $rank,
            'score' => $score,
            'streak' => $this->calculateStreaks($user->uuid)
        ];
    }

    private function resolveRank($score)
    {
        if ($score >= 300) return 'Elite';
        if ($score >= 150) return 'Athlete';
        if ($score >= 50) return 'Dedicated';
        return 'Recruit';
    }

    private function calculateStreaks($userUuid)
    {
        $allCompletedDates = PhysicalActivityTracker::where('user_uuid', $userUuid)
            ->where('status', 'completed')
            ->select(DB::raw('DISTINCT activity_date'))
            ->orderByDesc('activity_date')
            ->pluck('activity_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString());

        if ($allCompletedDates->isEmpty()) {
            return ['current' => 0, 'lifetime' => 0];
        }

        // 1. Current Streak
        $currentStreak = 0;
        $today = Carbon::today();
        $checkDate = $today->copy();

        // Allow 1 day gap if today isn't logged yet
        if (!$allCompletedDates->contains($checkDate->toDateString())) {
            $checkDate->subDay();
        }

        while ($allCompletedDates->contains($checkDate->toDateString())) {
            $currentStreak++;
            $checkDate->subDay();
        }

        // 2. Lifetime Max Streak
        $maxStreak = 0;
        $tempStreak = 0;
        $prevDate = null;

        // Sort ascending for max streak calculation
        $sortedDates = $allCompletedDates->reverse()->values();

        foreach ($sortedDates as $dateStr) {
            $curr = Carbon::parse($dateStr);
            if ($prevDate && $prevDate->copy()->addDay()->equalTo($curr)) {
                $tempStreak++;
            } else {
                $tempStreak = 1;
            }
            $maxStreak = max($maxStreak, $tempStreak);
            $prevDate = $curr;
        }

        return [
            'current' => $currentStreak,
            'lifetime' => $maxStreak
        ];
    }
}
