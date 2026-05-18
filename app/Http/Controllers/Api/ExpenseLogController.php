<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Services\BudgetService;
use App\Services\ExpenseService;
use App\Models\ExpenseCategory;
use App\Models\ExpenseLog;
use App\Models\BudgetPlanCycle;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ExpenseLogController extends Controller
{
    protected $budgetService;
    protected $expenseService;

    public function __construct(BudgetService $budgetService, ExpenseService $expenseService)
    {
        $this->budgetService = $budgetService;
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $date = $request->input('date', now()->toDateString());

            $cycle = $this->budgetService->resolveActiveCycle($user, $date);

            $planSummary = $cycle ? $this->budgetService->getCycleSummary($cycle) : null;
            $fixedCommitments = $this->expenseService->getFixedCommitments($user, $cycle);
            $dailyLogs = $this->expenseService->getDailyLogs($user, $date);

            return Response::json([
                'plan_summary' => $planSummary,
                'fixed_commitments' => $fixedCommitments,
                'daily_logs' => $dailyLogs,
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch expense logs',[__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function log(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $date = $request->input('expense_date', now()->toDateString());

            $cycle = BudgetPlanCycle::where('user_uuid', $user->uuid)
                ->where('cycle_start', '<=', $date)
                ->where('cycle_end', '>=', $date)
                ->where('status', 'active')
                ->first();

            throw_if(!$cycle, new \Exception("No active budget cycle found for this date. Please activate a plan first."));

            $categoryUuid = $request->input('category_uuid');

            if (!$categoryUuid) {
                $category = $this->expenseService->createExpenseCategory([
                    'category_name' => $request->input('category_name'),
                    'expense_period' => $request->boolean('is_fixed') ? 'fixed' : 'variable',
                    'default_amount' => $request->boolean('is_fixed') ? $request->input('amount') : 0
                ], $user, $cycle->plan_uuid);
                $categoryUuid = $category->uuid;
            }

            $log = ExpenseLog::updateOrCreate(
                [
                    'user_uuid' => $user->uuid,
                    'plan_cycle_uuid' => $cycle->uuid,
                    'category_uuid' => $categoryUuid,
                    'name' => $request->input('name'),
                    'expense_date' => $date,
                ],
                [
                    'amount' => $request->input('amount'),
                ]
            );

            $this->budgetService->syncCycleTotals($cycle);

            DB::commit();

            return Response::json([
                'message' => 'Expense logged successfully',
                'data' => $log
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();
            Helper::logError('Unable to log expense', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete an expense log and sync totals.
     */
    public function destroy($uuid)
    {
        try {
            $user = Auth::user();
            $log = ExpenseLog::where('uuid', $uuid)->where('user_uuid', $user->uuid)->firstOrFail();
            $cycleUuid = $log->plan_cycle_uuid;
            
            $log->delete();

            $cycle = BudgetPlanCycle::findByUuid($cycleUuid);
            $this->budgetService->syncCycleTotals($cycle);

            return response()->json(['message' => 'Expense deleted successfully'], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $e) {
            Helper::logError('Unable to delete expense', [__CLASS__, __FUNCTION__], $e);
            return response(['errors' => $e->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAvailableCategories(Request $request)
    {
        $user = Auth::user();
        $planUuid = $request->query('plan_uuid');

        $categories = ExpenseCategory::where('user_uuid', $user->uuid)
            ->where('expense_period', 'variable')
            ->when($planUuid, function ($query) use ($planUuid) {
                return $query->where('plan_uuid', $planUuid);
            })
            ->get()
            ->map(fn($cat) => [
                'uuid' => $cat->uuid,
                'name' => Helper::deslugifyCategory($cat->category_type),
                'type' => $cat->category_type
            ]);

        return response()->json(['categories' => $categories]);
    }
}
