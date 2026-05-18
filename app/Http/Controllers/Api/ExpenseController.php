<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Services\ExpenseService;
use App\Http\Resources\ExpenseResource;
use App\Models\ExpenseCategory;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function getExpense(Request $request)
    {
        try {
            $user = Auth::user();
            $planUuid = $request->query('plan_uuid');

            $fixedExpenses = ExpenseCategory::where('user_uuid', $user->uuid)
                ->where('expense_period', 'fixed')
                ->when($planUuid, function ($query) use ($planUuid) {
                    return $query->where('plan_uuid', $planUuid);
                })
                ->get();

            return ExpenseResource::collection($fixedExpenses);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to fetch fixed expenses', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveExpense(Request $request)
    {
        $request->validate([
            'fixed_expenses' => 'required|array',
            'fixed_expenses.*.category_name' => 'required|string',
            'fixed_expenses.*.default_amount' => 'nullable|numeric',
            'plan_uuid' => 'nullable|uuid',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = Auth::user();
                $this->expenseService->storeBulk($request->fixed_expenses, $request->plan_uuid);
                app(\App\Services\BudgetService::class)->recalculateCurrentFixedExpenses($user->uuid);
            });

            return Response::json([
                'message' => 'Fixed expenses saved successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to save fixed expenses', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteExpense($uuid)
    {
        try {
            DB::transaction(function () use ($uuid) {
                $user = Auth::user();
                $this->expenseService->deleteExpenseCategory($uuid);
                app(\App\Services\BudgetService::class)->recalculateCurrentFixedExpenses($user->uuid);
            });

            return Response::json([
                'message' => 'Expense category deleted successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to delete expense category', [__CLASS__, __FUNCTION__], $exception, ['uuid' => $uuid]);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
