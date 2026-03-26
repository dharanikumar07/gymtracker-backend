<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExpenseService;
use App\Models\ExpenseCategory;
use App\Models\ExpenseLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Get expense dashboard data.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $date = $request->query('date', now()->toDateString());
            $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
            
            // 1. Fixed Commitment
            $fixedCommitment = ExpenseCategory::where('user_uuid', $user->uuid)
                ->where('expense_period', 'fixed')
                ->sum('default_amount');

            // 2. Total Variable Spend (Current Month)
            $totalVariableSpend = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount');

            // 3. Total Monthly Spend
            $totalMonthlySpend = $fixedCommitment + $totalVariableSpend;

            // Fixed Expenses List
            $fixedExpenses = ExpenseCategory::where('user_uuid', $user->uuid)
                ->where('expense_period', 'fixed')
                ->get();

            // All Category Types
            $allCategories = ExpenseCategory::where('user_uuid', $user->uuid)
                ->select('category_type')
                ->distinct()
                ->pluck('category_type');
            
            // Daily Logs
            $dailyLogs = ExpenseLog::where('user_uuid', $user->uuid)
                ->where('expense_date', $date)
                ->orderBy('created_at', 'desc')
                ->get();

            // Filtered Logs (Recent Activity)
            $filteredLogs = ExpenseLog::where('user_uuid', $user->uuid)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->with('category')
                ->orderBy('expense_date', 'desc')
                ->get();

            // Budget Plans
            $budgetPlans = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'summary' => [
                    'variable_spend' => (int)$totalVariableSpend,
                    'fixed_commitment' => (int)$fixedCommitment,
                    'total_spend' => (int)$totalMonthlySpend,
                ],
                'fixed_expenses' => $fixedExpenses,
                'all_categories' => $allCategories,
                'daily_logs' => $dailyLogs,
                'filtered_logs' => $filteredLogs,
                'budget_plans' => $budgetPlans
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch expenses', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Log a daily expense (POST for Create/Update).
     */
    public function log(Request $request)
    {
        $request->validate([
            'uuid' => 'nullable|uuid',
            'category_type' => 'required|string',
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'expense_period' => 'required|in:fixed,variable',
            'expense_date' => 'nullable|date',
        ]);

        try {
            $data = $this->expenseService->logExpense($request->all());

            return response()->json([
                'message' => 'Expense updated/logged successfully',
                'data' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to log daily expense', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Budget Plan.
     */
    public function saveBudgetPlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        return DB::transaction(function () use ($request) {
            $user = Auth::user();

            if ($request->is_active) {
                Plan::where('user_uuid', $user->uuid)
                    ->where('type', 'expense')
                    ->update(['is_active' => false]);
            }

            $plan = Plan::create([
                'user_uuid' => $user->uuid,
                'name' => $request->name,
                'type' => 'expense',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active,
                'meta_data' => ['amount' => (float) $request->amount]
            ]);

            return response()->json([
                'message' => 'Budget plan created successfully',
                'data' => $plan
            ], HttpFoundationResponse::HTTP_CREATED);
        });
    }

    /**
     * Get Budget Plan Status with calculated stats.
     */
    public function getBudgetPlans()
    {
        try {
            $user = Auth::user();

            $budgetPlans = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $budgetPlans
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch budget plans', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Budget Plan Status with calculated stats.
     */
    public function getBudgetPlanStatus($uuid)
    {
        try {
            $user = Auth::user();
            $today = now()->toDateString();

            $budgetPlan = Plan::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->firstOrFail();

            $startDate = $budgetPlan->start_date;
            $endDate = $budgetPlan->end_date;
            $budgetAmount = $budgetPlan->meta_data['amount'] ?? 0;

            // Calculate total days
            $totalDays = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;

            // Calculate days passed
            if ($today < $startDate) {
                // Budget hasn't started
                $status = 'upcoming';
                $daysPassed = 0;
                $remainingDays = (int) $totalDays;
                $totalSpent = 0;
                $remainingAmount = $budgetAmount;
                $expectedSpent = 0;
                $dailyAllowance = $budgetAmount / $totalDays;
            } elseif ($today > $endDate) {
                // Budget ended
                $status = 'ended';
                $daysPassed = (int) $totalDays;
                $remainingDays = 0;
                $expectedSpent = $budgetAmount;
                $totalSpent = ExpenseLog::where('user_uuid', $user->uuid)
                    ->whereBetween('expense_date', [$startDate, $endDate])
                    ->sum('amount');
                $remainingAmount = $budgetAmount - $totalSpent;
                $dailyAllowance = 0;
            } else {
                // Budget is active
                $status = 'active';
                $daysPassed = (strtotime($today) - strtotime($startDate)) / (60 * 60 * 24) + 1;
                $remainingDays = (strtotime($endDate) - strtotime($today)) / (60 * 60 * 24);
                $expectedSpent = ($budgetAmount / $totalDays) * $daysPassed;
                
                $totalSpent = ExpenseLog::where('user_uuid', $user->uuid)
                    ->whereBetween('expense_date', [$startDate, $today])
                    ->sum('amount');
                
                $remainingAmount = $budgetAmount - $totalSpent;
                $dailyAllowance = $remainingDays > 0 ? $remainingAmount / $remainingDays : 0;
            }

            // Determine color status
            if ($status === 'upcoming') {
                $color = 'blue';
                $statusText = 'Starts in ' . (strtotime($startDate) - strtotime($today)) / (60 * 60 * 24) . ' days';
            } elseif ($status === 'ended') {
                $color = $remainingAmount >= 0 ? 'green' : 'red';
                $statusText = $remainingAmount >= 0 
                    ? 'Completed - ₹' . number_format($remainingAmount, 0) . ' saved' 
                    : 'Over by ₹' . number_format(abs($remainingAmount), 0);
            } else {
                // Active status based on spending
                if ($totalSpent <= $expectedSpent * 0.8) {
                    $color = 'green';
                    $statusText = 'On track';
                } elseif ($totalSpent <= $expectedSpent * 1.0) {
                    $color = 'yellow';
                    $statusText = 'Warning';
                } else {
                    $color = 'red';
                    $statusText = 'Over budget';
                }
            }

            return response()->json([
                'data' => [
                    'is_active' => $budgetPlan->is_active,
                    'plan' => $budgetPlan,
                    'stats' => [
                        'budget_amount' => (float) $budgetAmount,
                        'total_days' => (int) $totalDays,
                        'days_passed' => (int) $daysPassed,
                        'remaining_days' => (int) $remainingDays,
                        'total_spent' => (float) $totalSpent,
                        'remaining_amount' => (float) $remainingAmount,
                        'expected_spent' => (float) $expectedSpent,
                        'daily_allowance' => (float) $dailyAllowance,
                    ],
                    'status' => [
                        'type' => $status,
                        'color' => $color,
                        'text' => $statusText,
                    ]
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to fetch budget plan status', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a Budget Plan.
     */
    public function updateBudgetPlan(Request $request, $uuid)
    {
        $request->validate([
            'name' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'is_active' => 'sometimes|required|boolean',
        ]);

        return DB::transaction(function () use ($request, $uuid) {
            $user = Auth::user();

            $plan = Plan::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->firstOrFail();

            if ($request->boolean('is_active')) {
                Plan::where('user_uuid', $user->uuid)
                    ->where('type', 'expense')
                    ->where('uuid', '!=', $uuid)
                    ->update(['is_active' => false]);
            }

            $updateData = array_filter([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->boolean('is_active'),
            ], fn($value) => $value !== null);

            if ($request->has('amount')) {
                $updateData['meta_data'] = ['amount' => (float) $request->amount];
            }

            $plan->update($updateData);

            return response()->json([
                'message' => 'Budget plan updated successfully',
                'data' => $plan->fresh()
            ], HttpFoundationResponse::HTTP_OK);
        });
    }

    /**
     * Delete a Budget Plan.
     */
    public function deleteBudgetPlan($uuid)
    {
        try {
            $user = Auth::user();

            $plan = Plan::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->firstOrFail();

            $plan->delete();

            return response()->json([
                'message' => 'Budget plan deleted successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to delete budget plan', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Activate a Budget Plan.
     */
    public function activateBudgetPlan($uuid)
    {
        return DB::transaction(function () use ($uuid) {
            $user = Auth::user();

            Plan::where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->update(['is_active' => false]);

            $plan = Plan::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->where('type', 'expense')
                ->firstOrFail();

            $plan->update(['is_active' => true]);

            return response()->json([
                'message' => 'Budget plan activated successfully',
                'data' => $plan->fresh()
            ], HttpFoundationResponse::HTTP_OK);
        });
    }

    /**
     * Delete an expense log.
     */
    public function destroy($uuid)
    {
        try {
            $user = Auth::user();
            $log = ExpenseLog::where('uuid', $uuid)->where('user_uuid', $user->uuid)->firstOrFail();
            $log->delete();

            return response()->json([
                'message' => 'Expense deleted successfully',
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            \App\Http\Helpers\Helper::logError('Unable to delete expense', [__CLASS__, __FUNCTION__], $exception, ['uuid' => $uuid]);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
