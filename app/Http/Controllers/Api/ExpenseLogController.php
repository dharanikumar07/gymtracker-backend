<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Services\ExpenseService;
use App\Models\ExpenseCategory;
use App\Models\ExpenseLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ExpenseLogController extends Controller
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
