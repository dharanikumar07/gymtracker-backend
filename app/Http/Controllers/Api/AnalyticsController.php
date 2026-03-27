<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use App\Models\PhysicalActivityTracker;
use App\Models\DietLog;
use App\Models\ExpenseLog;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AnalyticsController extends Controller
{
    public function overview()
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $currentDay = strtolower($today->format('D'));
            $startOfWeekDay = strtolower($startOfWeek->format('D'));
            $endOfWeekDay = strtolower($endOfWeek->format('D'));

            $activeFitnessPlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'physical_activity')
                ->where('is_active', true)
                ->first();

            $activeDietPlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->where('is_active', true)
                ->first();

            $activeBudgetPlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->where('is_active', true)
                ->first();

            $fitnessThisWeek = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereBetween('activity_date', [$startOfWeek, $endOfWeek])
                ->count();

            $fitnessStreak = $this->calculateFitnessStreak($user->uuid);

            $trackerMonthData = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereBetween('activity_date', [$startOfMonth, $endOfMonth])
                ->get();

            $fitnessTotalVolume = $trackerMonthData->sum(function ($tracker) {
                return $tracker->metrics_data['weight'] ?? 0;
            });

            $dietToday = DietLog::where('user_uuid', $user->uuid)
                ->where('day', $currentDay)
                ->get();

            $totalCaloriesConsumed = $dietToday->sum('calories') ?? 0;
            $totalProteinConsumed = $dietToday->sum('protein') ?? 0;
            $totalCarbsConsumed = $dietToday->sum('carbs') ?? 0;
            $totalFatConsumed = $dietToday->sum('fats') ?? 0;

            $expenseToday = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereDate('expense_date', $today)
                ->sum('amount') ?? 0;

            $expenseThisMonth = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->sum('amount') ?? 0;

            $budgetAmount = $activeBudgetPlan->meta_data['amount'] ?? 0;
            $budgetRemaining = $budgetAmount - $expenseThisMonth;
            $budgetUsedPercent = $budgetAmount > 0 ? ($expenseThisMonth / $budgetAmount) * 100 : 0;

            $fitnessProgress = $activeFitnessPlan ? min(($fitnessThisWeek / 5) * 100, 100) : 0;
            $dietProgress = $activeDietPlan ? min(($totalCaloriesConsumed / ($activeDietPlan->meta_data['target_calories'] ?? 2000)) * 100, 100) : 0;
            $expenseProgress = $activeBudgetPlan ? min($budgetUsedPercent, 100) : 0;
            $overallProgress = round(($fitnessProgress + $dietProgress + (100 - $expenseProgress)) / 3, 1);

            return response()->json([
                'data' => [
                    'fitness' => [
                        'has_plan' => (bool) $activeFitnessPlan,
                        'plan_name' => $activeFitnessPlan->name ?? null,
                        'this_week' => $fitnessThisWeek,
                        'streak' => $fitnessStreak,
                        'total_volume' => (int) $fitnessTotalVolume,
                        'progress' => (int) $fitnessProgress,
                    ],
                    'diet' => [
                        'has_plan' => (bool) $activeDietPlan,
                        'plan_name' => $activeDietPlan->name ?? null,
                        'target_calories' => $activeDietPlan->meta_data['target_calories'] ?? 2000,
                        'consumed' => (int) $totalCaloriesConsumed,
                        'protein' => (int) $totalProteinConsumed,
                        'carbs' => (int) $totalCarbsConsumed,
                        'fat' => (int) $totalFatConsumed,
                        'progress' => (int) $dietProgress,
                    ],
                    'expenses' => [
                        'has_plan' => (bool) $activeBudgetPlan,
                        'plan_name' => $activeBudgetPlan->name ?? null,
                        'budget' => (float) $budgetAmount,
                        'spent' => (int) $expenseThisMonth,
                        'remaining' => (int) $budgetRemaining,
                        'spent_today' => (int) $expenseToday,
                        'progress' => (int) $expenseProgress,
                    ],
                    'overall' => [
                        'progress' => (float) $overallProgress,
                        'fitness_progress' => (int) $fitnessProgress,
                        'diet_progress' => (int) $dietProgress,
                        'expense_progress' => (int) $expenseProgress,
                    ]
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch analytics overview', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function fitness(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->query('period', 'week');
            
            $startDate = $period === 'month' 
                ? Carbon::now()->startOfMonth() 
                : Carbon::now()->startOfWeek();
            $endDate = Carbon::today();

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'physical_activity')
                ->where('is_active', true)
                ->first();

            $dailyWorkouts = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->selectRaw('DATE(activity_date) as day, COUNT(*) as count')
                ->groupBy('day')
                ->pluck('count', 'day')
                ->toArray();

            $days = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dayKey = $current->format('Y-m-d');
                $days[] = [
                    'date' => $dayKey,
                    'day_name' => $current->format('D'),
                    'day_number' => $current->day,
                    'workouts' => $dailyWorkouts[$dayKey] ?? 0,
                ];
                $current->addDay();
            }

            $trackerData = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->whereBetween('activity_date', [$startDate, $endDate])
                ->get();

            $weeklyStats = [
                'total_workouts' => $trackerData->count(),
                'total_volume' => $trackerData->sum(fn($t) => $t->metrics_data['weight'] ?? 0),
                'total_reps' => $trackerData->sum(fn($t) => $t->metrics_data['reps'] ?? 0),
                'avg_duration' => $trackerData->avg(fn($t) => $t->metrics_data['duration'] ?? 0) ?? 0,
            ];

            $slotUuids = $trackerData->pluck('slot_uuid')->filter()->unique()->toArray();
            $muscleGroups = [];
            
            if (count($slotUuids) > 0) {
                $slots = PhysicalActivitySlot::whereIn('uuid', $slotUuids)->get();
                $slotWeights = $trackerData->groupBy('slot_uuid');
                
                $grouped = $slots->groupBy(fn($slot) => $slot->meta_data['muscle_group'] ?? 'Other');
                
                foreach ($grouped as $muscle => $group) {
                    $totalWeight = 0;
                    foreach ($group as $slot) {
                        $trackers = $slotWeights->get($slot->uuid, collect());
                        $totalWeight += $trackers->sum(fn($t) => $t->metrics_data['weight'] ?? 0);
                    }
                    $muscleGroups[] = [
                        'muscle_group' => $muscle,
                        'count' => $group->count(),
                        'total_weight' => (int) $totalWeight,
                    ];
                }
            }

            return response()->json([
                'data' => [
                    'active_plan' => $activePlan ? [
                        'uuid' => $activePlan->uuid,
                        'name' => $activePlan->name,
                    ] : null,
                    'period' => $period,
                    'days' => $days,
                    'stats' => $weeklyStats,
                    'muscle_groups' => $muscleGroups,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch fitness analytics', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function diet(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->query('period', 'week');
            
            $startDate = $period === 'month' 
                ? Carbon::now()->startOfMonth() 
                : Carbon::now()->startOfWeek();
            $endDate = Carbon::today();
            $today = Carbon::today();
            $currentDay = strtolower($today->format('D'));

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->where('is_active', true)
                ->first();

            $targetCalories = $activePlan->meta_data['target_calories'] ?? 2000;
            $targetProtein = $activePlan->meta_data['target_protein'] ?? 150;
            $targetCarbs = $activePlan->meta_data['target_carbs'] ?? 250;
            $targetFat = $activePlan->meta_data['target_fat'] ?? 65;

            $daysEnum = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            $startDayIndex = $startDate->dayOfWeek === 0 ? 6 : $startDate->dayOfWeek - 1;
            $endDayIndex = $endDate->dayOfWeek === 0 ? 6 : $endDate->dayOfWeek - 1;
            
            $dayLogs = DietLog::where('user_uuid', $user->uuid)
                ->whereIn('day', array_slice($daysEnum, $startDayIndex, $endDayIndex - $startDayIndex + 1))
                ->selectRaw('day, SUM(calories) as calories, SUM(protein) as protein, SUM(carbs) as carbs, SUM(fats) as fats')
                ->groupBy('day')
                ->get()
                ->keyBy('day')
                ->toArray();

            $days = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dayKey = strtolower($current->format('D'));
                $log = $dayLogs[$dayKey] ?? null;
                $calories = $log['calories'] ?? 0;
                $days[] = [
                    'date' => $current->format('Y-m-d'),
                    'day_name' => $current->format('D'),
                    'day_number' => $current->day,
                    'is_today' => $current->isToday(),
                    'calories' => (int) $calories,
                    'protein' => (int) ($log['protein'] ?? 0),
                    'carbs' => (int) ($log['carbs'] ?? 0),
                    'fat' => (int) ($log['fats'] ?? 0),
                    'progress' => $targetCalories > 0 ? min(($calories / $targetCalories) * 100, 100) : 0,
                ];
                $current->addDay();
            }

            $todayLog = DietLog::where('user_uuid', $user->uuid)
                ->where('day', $currentDay)
                ->get();

            $weeklyStats = [
                'avg_calories' => count($dayLogs) > 0 ? round(array_sum(array_column($dayLogs, 'calories')) / count($dayLogs)) : 0,
                'avg_protein' => count($dayLogs) > 0 ? round(array_sum(array_column($dayLogs, 'protein')) / count($dayLogs)) : 0,
                'avg_carbs' => count($dayLogs) > 0 ? round(array_sum(array_column($dayLogs, 'carbs')) / count($dayLogs)) : 0,
                'avg_fat' => count($dayLogs) > 0 ? round(array_sum(array_column($dayLogs, 'fats')) / count($dayLogs)) : 0,
                'days_logged' => count($dayLogs),
            ];

            $mealBreakdown = [];
            $mealLogs = DietLog::where('user_uuid', $user->uuid)
                ->whereIn('day', array_slice($daysEnum, $startDayIndex, $endDayIndex - $startDayIndex + 1))
                ->get();

            $groupedMeals = $mealLogs->groupBy(fn($log) => $log->nutrition_data['meal_type'] ?? 'Other');
            foreach ($groupedMeals as $mealType => $logs) {
                $mealBreakdown[] = [
                    'meal_type' => $mealType,
                    'count' => $logs->count(),
                    'calories' => (int) $logs->sum('calories'),
                ];
            }

            return response()->json([
                'data' => [
                    'active_plan' => $activePlan ? [
                        'uuid' => $activePlan->uuid,
                        'name' => $activePlan->name,
                        'targets' => [
                            'calories' => $targetCalories,
                            'protein' => $targetProtein,
                            'carbs' => $targetCarbs,
                            'fat' => $targetFat,
                        ]
                    ] : null,
                    'today' => [
                        'calories' => (int) ($todayLog->sum('calories') ?? 0),
                        'protein' => (int) ($todayLog->sum('protein') ?? 0),
                        'carbs' => (int) ($todayLog->sum('carbs') ?? 0),
                        'fat' => (int) ($todayLog->sum('fats') ?? 0),
                    ],
                    'period' => $period,
                    'days' => $days,
                    'stats' => $weeklyStats,
                    'meal_breakdown' => $mealBreakdown,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch diet analytics', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function expenses(Request $request)
    {
        try {
            $user = Auth::user();
            $period = $request->query('period', 'month');
            
            $startDate = $period === 'week' 
                ? Carbon::now()->startOfWeek() 
                : Carbon::now()->startOfMonth();
            $endDate = Carbon::today();

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->where('is_active', true)
                ->first();

            $budgetAmount = $activePlan->meta_data['amount'] ?? 0;

            $dailyExpenses = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->selectRaw('DATE(expense_date) as day, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('day')
                ->get()
                ->keyBy('day')
                ->toArray();

            $days = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dayKey = $current->format('Y-m-d');
                $log = $dailyExpenses[$dayKey] ?? null;
                $total = $log['total'] ?? 0;
                $days[] = [
                    'date' => $dayKey,
                    'day_name' => $current->format('D'),
                    'day_number' => $current->day,
                    'amount' => (int) $total,
                    'count' => (int) ($log['count'] ?? 0),
                ];
                $current->addDay();
            }

            $categoryBreakdown = ExpenseLog::where('expense_logs.user_uuid', $user->uuid)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->join('expense_categories', 'expense_logs.category_uuid', '=', 'expense_categories.uuid')
                ->selectRaw('expense_categories.category_type, SUM(expense_logs.amount) as total, COUNT(*) as count')
                ->groupBy('expense_categories.category_type')
                ->orderBy('total', 'desc')
                ->get()
                ->map(fn($item) => [
                    'category' => $item->category_type,
                    'total' => (int) $item->total,
                    'count' => (int) $item->count,
                    'percentage' => 0,
                ])
                ->toArray();

            $totalExpenses = array_sum(array_column($categoryBreakdown, 'total'));
            foreach ($categoryBreakdown as &$item) {
                $item['percentage'] = $totalExpenses > 0 ? round(($item['total'] / $totalExpenses) * 100, 1) : 0;
            }

            $monthlyStats = [
                'total_spent' => (int) $totalExpenses,
                'budget' => (float) $budgetAmount,
                'remaining' => (int) ($budgetAmount - $totalExpenses),
                'avg_daily' => count($dailyExpenses) > 0 ? round($totalExpenses / count($dailyExpenses)) : 0,
                'transactions' => ExpenseLog::where('user_uuid', $user->uuid)
                    ->whereBetween('expense_date', [$startDate, $endDate])
                    ->count(),
            ];

            $biggestExpense = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->orderBy('amount', 'desc')
                ->first();

            return response()->json([
                'data' => [
                    'active_plan' => $activePlan ? [
                        'uuid' => $activePlan->uuid,
                        'name' => $activePlan->name,
                        'budget' => (float) $budgetAmount,
                    ] : null,
                    'period' => $period,
                    'days' => $days,
                    'stats' => $monthlyStats,
                    'category_breakdown' => $categoryBreakdown,
                    'biggest_expense' => $biggestExpense ? [
                        'name' => $biggestExpense->name,
                        'amount' => (int) $biggestExpense->amount,
                        'date' => $biggestExpense->expense_date,
                    ] : null,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch expense analytics', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function calculateFitnessStreak($userUuid)
    {
        $streak = 0;
        $current = Carbon::today();

        while ($streak < 365) {
            $hasWorkout = PhysicalActivityTracker::where('user_uuid', $userUuid)
                ->whereDate('activity_date', $current)
                ->exists();

            if ($hasWorkout) {
                $streak++;
                $current->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}
