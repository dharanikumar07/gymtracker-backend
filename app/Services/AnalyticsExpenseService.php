<?php

namespace App\Services;

use App\Models\ExpenseLog;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AnalyticsExpenseService
{
    private function getBaseQuery($user, array $params)
    {
        $query = ExpenseLog::with(['category'])
            ->where('user_uuid', $user->uuid);

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $query->whereBetween('expense_date', [$params['start_date'], $params['end_date']]);
        } elseif (!empty($params['start_date'])) {
            $query->where('expense_date', $params['start_date']);
        }

        return $query;
    }

    public function getExpenseLogs($user, array $params)
    {
        $startDate = Carbon::parse($params['start_date']);
        $endDate = !empty($params['end_date']) ? Carbon::parse($params['end_date']) : $startDate->copy();
        
        $logs = $this->getBaseQuery($user, $params)
            ->orderBy('expense_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $fixedExpenses = [];
        $variableExpenses = [];

        foreach ($logs as $log) {
            $data = [
                'uuid' => $log->uuid,
                'category_name' => $log->category->category_type ?? 'Unknown',
                'amount' => $log->amount,
                'expense_date' => Carbon::parse($log->expense_date)->toDateString(),
                'type' => $log->category->expense_period ?? 'variable'
            ];

            if (($log->category->expense_period ?? 'variable') === 'fixed') {
                $fixedExpenses[] = $data;
            } else {
                $variableExpenses[] = $data;
            }
        }

        return [
            'fixed' => $fixedExpenses,
            'variable' => $variableExpenses,
            'total' => $logs->sum('amount')
        ];
    }

    public function getExpenseSummary($user, array $params)
    {
        $baseQuery = $this->getBaseQuery($user, $params);
        $totalSpent = (int) $baseQuery->sum('amount');
        $transactionCount = $baseQuery->count();
        
        $highestCategory = ExpenseLog::where('user_uuid', $user->uuid)
            ->whereBetween('expense_date', [$params['start_date'], $params['end_date'] ?? $params['start_date']])
            ->select('category_uuid', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category_uuid')
            ->orderByDesc('total_amount')
            ->first();

        $highestCategoryName = 'N/A';
        if ($highestCategory) {
            $cat = ExpenseCategory::where('uuid', $highestCategory->category_uuid)->first();
            $highestCategoryName = $cat->category_type ?? 'Unknown';
        }

        $activeDays = $baseQuery->distinct('expense_date')->count();

        return [
            'total_spent' => $totalSpent,
            'transaction_count' => $transactionCount,
            'avg_per_day' => $activeDays > 0 ? round($totalSpent / $activeDays, 2) : 0,
            'highest_category' => $highestCategoryName
        ];
    }

    public function getExpenseTrend($user, array $params)
    {
        $periodType = $params['period_type'] ?? 'month';
        $lookback = $params['lookback'] ?? '4';
        $categoryUuid = $params['category_uuid'] ?? null;
        
        $now = Carbon::now();
        $data = [];

        $count = $this->getLookbackCount($user, $lookback, $periodType);

        for ($i = $count - 1; $i >= 0; $i--) {
            if ($periodType === 'month') {
                $target = $now->copy()->subMonths($i);
                $start = $target->copy()->startOfMonth();
                $end = $target->copy()->endOfMonth();
                $label = $target->format('M Y');
            } elseif ($periodType === 'week') {
                $target = $now->copy()->subWeeks($i);
                $start = $target->copy()->startOfWeek();
                $end = $target->copy()->endOfWeek();
                $label = 'W' . $target->weekOfYear . ' ' . $target->format('y');
            } else { // day
                $start = $now->copy()->subDays($i)->startOfDay();
                $end = $start->copy()->endOfDay();
                $label = $start->format('d M');
            }

            $query = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereBetween('expense_date', [$start, $end]);

            if ($categoryUuid) {
                $query->where('category_uuid', $categoryUuid);
            }

            $amount = (int) $query->sum('amount');

            $data[] = [
                'label' => $label,
                'amount' => $amount,
                'period' => $start->toDateString()
            ];
        }

        return $data;
    }

    public function getExpenseDistribution($user, array $params)
    {
        $periodType = $params['period_type'] ?? 'month';
        $lookback = $params['lookback'] ?? '4';
        
        $now = Carbon::now();
        $count = $this->getLookbackCount($user, $lookback, $periodType);

        if ($periodType === 'month') {
            $start = $now->copy()->subMonths($count - 1)->startOfMonth();
        } elseif ($periodType === 'week') {
            $start = $now->copy()->subWeeks($count - 1)->startOfWeek();
        } else { // day
            $start = $now->copy()->subDays($count - 1)->startOfDay();
        }
        
        $end = $now->copy()->endOfDay();

        $categoryAmounts = ExpenseLog::with('category')
            ->where('user_uuid', $user->uuid)
            ->whereBetween('expense_date', [$start, $end])
            ->select('category_uuid', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category_uuid')
            ->get();

        $totalVolume = $categoryAmounts->sum('total_amount');
        
        $distribution = $categoryAmounts->map(function ($item) use ($totalVolume) {
            return [
                'name' => $item->category->category_type ?? 'Unknown',
                'value' => $totalVolume > 0 ? round(($item->total_amount / $totalVolume) * 100, 2) : 0,
                'raw_amount' => (int) $item->total_amount
            ];
        })->values();

        return [
            'label' => $lookback === 'all_time' ? 'All Time' : ($lookback . ' ' . $periodType . 's'),
            'data' => $distribution,
            'total_amount' => (int) $totalVolume
        ];
    }

    public function getAvailableCategories($user)
    {
        return ExpenseCategory::where('user_uuid', $user->uuid)
            ->select('uuid', 'category_type', 'expense_period')
            ->get()
            ->map(function ($cat) {
                return [
                    'uuid' => $cat->uuid,
                    'name' => $cat->category_type,
                    'type' => $cat->expense_period
                ];
            });
    }

    private function getLookbackCount($user, $lookback, $type)
    {
        if ($lookback === 'all_time') {
            $userJoined = Carbon::parse($user->created_at);
            $now = Carbon::now();

            if ($type === 'month') {
                return $userJoined->diffInMonths($now) + 1;
            } elseif ($type === 'week') {
                return $userJoined->diffInWeeks($now) + 1;
            } else { // day
                return $userJoined->diffInDays($now) + 1;
            }
        }

        if (is_numeric($lookback)) return (int)$lookback;
        
        if ($lookback === 'this_year') {
            return $type === 'month' ? Carbon::now()->month : Carbon::now()->weekOfYear;
        }

        return 4;
    }
}
