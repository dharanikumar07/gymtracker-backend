<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PhysicalActivityTracker;
use App\Models\ExpenseLog;
use App\Services\Checklist\ChecklistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            $data = [
                'user' => $this->getUserData($user),
                'quick_actions' => $this->getQuickActions(),
                'today' => $this->getTodayStats($user),
                'streak' => $this->getStreakData($user),
                'recent' => $this->getRecentActivity($user),
                'weekly_chart' => $this->getWeeklyChart($user),
            ];

            return response()->json(['data' => $data], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch dashboard data', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checklist(ChecklistService $checklistService)
    {
        try {
            $user = Auth::user();
            $data = $checklistService->getChecklistSteps($user);

            return response()->json(['data' => $data], HttpFoundationResponse::HTTP_OK);
        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch checklist data', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getUserData($user)
    {
        return [
            'name' => $user->name,
            'avatar' => $user->avatar ?? null,
            'is_onboarded' => (bool) $user->is_onboarded,
        ];
    }

    private function getQuickActions()
    {
        return [
            ['id' => 'workout', 'label' => 'Workout', 'icon' => 'dumbbell', 'route' => '/track-progress/workout'],
            ['id' => 'expense', 'label' => 'Expense', 'icon' => 'wallet', 'route' => '/track-expense/log'],
            ['id' => 'plan', 'label' => 'Plan', 'icon' => 'clipboard-list', 'route' => '/track-progress/routine'],
        ];
    }

    private function getTodayStats($user)
    {
        $fitnessPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'physical_activity')->where('is_active', true)->first();
        $budgetPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'budget')->where('is_active', true)->first();

        $workoutCount = PhysicalActivityTracker::where('user_uuid', $user->uuid)
            ->whereDate('activity_date', Carbon::today())
            ->count();

        $expenseToday = (int) (ExpenseLog::where('user_uuid', $user->uuid)
            ->whereDate('expense_date', Carbon::today())
            ->sum('amount') ?? 0);

        $budgetAmount = $budgetPlan->meta_data['amount'] ?? 0;

        return [
            'fitness' => [
                'has_plan' => (bool) $fitnessPlan,
                'completed' => $workoutCount,
                'target' => 1,
                'percentage' => min($workoutCount * 100, 100),
            ],
            'budget' => [
                'has_plan' => (bool) $budgetPlan,
                'spent' => $expenseToday,
                'daily_limit' => $budgetAmount > 0 ? round($budgetAmount / 30, 2) : 0,
                'percentage' => $budgetAmount > 0 ? min(($expenseToday / ($budgetAmount / 30)) * 100, 100) : 0,
            ],
        ];
    }

    private function getStreakData($user)
    {
        $streak = 0;
        $current = Carbon::today();

        while ($streak < 365) {
            $hasWorkout = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereDate('activity_date', $current)
                ->exists();

            if ($hasWorkout) { $streak++; $current->subDay(); } else { break; }
        }

        $weekStart = Carbon::now()->startOfWeek();
        $weekDays = [];
        
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $hasWorkout = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereDate('activity_date', $day)
                ->exists();
            
            $weekDays[] = [
                'day' => $day->format('D'),
                'date' => $day->format('Y-m-d'),
                'completed' => $hasWorkout,
                'is_today' => $day->isToday(),
            ];
        }

        $weekProgress = count(array_filter($weekDays, fn($d) => $d['completed'])) / 7 * 100;

        return [
            'current_streak' => $streak,
            'week_days' => $weekDays,
            'week_progress' => (int) $weekProgress,
        ];
    }

    private function getRecentActivity($user)
    {
        $recentWorkouts = PhysicalActivityTracker::with('slot')
            ->where('user_uuid', $user->uuid)
            ->orderBy('activity_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($w) => [
                'type' => 'workout',
                'title' => 'You logged ' . ($w->slot->exercise_name ?? 'a workout'),
                'date' => $w->activity_date,
            ]);

        $recentExpenses = ExpenseLog::with('category')
            ->where('user_uuid', $user->uuid)
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($e) => [
                'type' => 'expense',
                'title' => 'You logged ' . ($e->category->category_type ?? 'an expense'),
                'date' => Carbon::parse($e->expense_date),
            ]);

        $merged = $recentWorkouts
            ->concat($recentExpenses)
            ->sortByDesc('date')
            ->values();

        return $merged->toArray();
    }

    private function getWeeklyChart($user)
    {
        $weekStart = Carbon::now()->startOfWeek();
        $chart = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);

            $workouts = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereDate('activity_date', $day)
                ->count();

            $expenses = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereDate('expense_date', $day)
                ->count();

            $chart[] = [
                'day' => $day->format('D'),
                'date' => $day->format('Y-m-d'),
                'workouts' => $workouts,
                'expenses' => $expenses,
            ];
        }

        return $chart;
    }
}
