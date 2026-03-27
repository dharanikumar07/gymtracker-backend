<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PhysicalActivityTracker;
use App\Models\DietLog;
use App\Models\ExpenseLog;
use App\Models\User;
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
            $today = Carbon::today();
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $currentDay = strtolower($today->format('D'));

            $data = [
                'user' => $this->getUserData($user),
                'quick_actions' => $this->getQuickActions(),
                'today' => $this->getTodayStats($user, $currentDay),
                'streak' => $this->getStreakData($user),
                'quick_start' => $this->getQuickStartStatus($user),
                'recent' => $this->getRecentActivity($user),
            ];

            return response()->json(['data' => $data], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch dashboard data', [__CLASS__, __FUNCTION__], $exception);
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
            ['id' => 'workout', 'label' => 'Workout', 'icon' => 'dumbbell', 'route' => '/workout'],
            ['id' => 'diet', 'label' => 'Diet', 'icon' => 'utensils', 'route' => '/diet'],
            ['id' => 'expense', 'label' => 'Expense', 'icon' => 'wallet', 'route' => '/expenses'],
            ['id' => 'plan', 'label' => 'Plan', 'icon' => 'clipboard-list', 'route' => '/plan'],
            ['id' => 'billing', 'label' => 'Billing', 'icon' => 'credit-card', 'route' => '/billing'],
        ];
    }

    private function getTodayStats($user, $currentDay)
    {
        $fitnessPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'physical_activity')->where('is_active', true)->first();
        $dietPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'diet')->where('is_active', true)->first();
        $budgetPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'expense')->where('is_active', true)->first();

        $workoutCount = PhysicalActivityTracker::where('user_uuid', $user->uuid)
            ->whereDate('activity_date', Carbon::today())
            ->count();

        $dietLogs = DietLog::where('user_uuid', $user->uuid)->where('day', $currentDay)->get();
        $caloriesConsumed = (int) ($dietLogs->sum('calories') ?? 0);
        $targetCalories = $dietPlan->meta_data['target_calories'] ?? 2000;

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
            'diet' => [
                'has_plan' => (bool) $dietPlan,
                'consumed' => $caloriesConsumed,
                'target' => $targetCalories,
                'percentage' => $targetCalories > 0 ? min(($caloriesConsumed / $targetCalories) * 100, 100) : 0,
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

    private function getQuickStartStatus($user)
    {
        $hasProfile = !empty($user->name) && !empty($user->email);
        $hasFitnessPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'physical_activity')->where('is_active', true)->exists();
        $hasDietPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'diet')->where('is_active', true)->exists();
        $hasBudgetPlan = Plan::where('user_uuid', $user->uuid)->where('type', 'expense')->where('is_active', true)->exists();

        $items = [
            ['id' => 'profile', 'title' => 'Complete your profile', 'completed' => $hasProfile],
            ['id' => 'fitness', 'title' => 'Set up workout plan', 'completed' => $hasFitnessPlan],
            ['id' => 'diet', 'title' => 'Set diet goals', 'completed' => $hasDietPlan],
            ['id' => 'budget', 'title' => 'Add budget plan', 'completed' => $hasBudgetPlan],
        ];

        $completed = count(array_filter($items, fn($i) => $i['completed']));
        $total = count($items);

        return [
            'items' => $items,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total > 0 ? ($completed / $total) * 100 : 0,
            'is_complete' => $completed === $total,
        ];
    }

    private function getRecentActivity($user)
    {
        $activities = [];

        $recentWorkouts = PhysicalActivityTracker::where('user_uuid', $user->uuid)
            ->orderBy('activity_date', 'desc')
            ->limit(2)
            ->get()
            ->map(fn($w) => [
                'type' => 'workout',
                'title' => $w->slot->exercise_name ?? 'Workout',
                'value' => ($w->metrics_data['weight'] ?? 0) . ' kg',
                'date' => $w->activity_date,
                'icon' => 'dumbbell',
            ]);

        $recentDiet = DietLog::where('user_uuid', $user->uuid)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get()
            ->map(fn($d) => [
                'type' => 'diet',
                'title' => $d->actual_food_name ?? 'Meal',
                'value' => ($d->calories ?? 0) . ' kcal',
                'date' => $d->created_at,
                'icon' => 'utensils',
            ]);

        $recentExpenses = ExpenseLog::where('user_uuid', $user->uuid)
            ->orderBy('expense_date', 'desc')
            ->limit(2)
            ->get()
            ->map(fn($e) => [
                'type' => 'expense',
                'title' => $e->name,
                'value' => '₹' . $e->amount,
                'date' => $e->expense_date,
                'icon' => 'wallet',
            ]);

        $merged = $recentWorkouts
            ->concat($recentDiet)
            ->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(3)
            ->values();

        return $merged->toArray();
    }
}
