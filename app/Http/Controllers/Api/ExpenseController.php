<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Services\ExpenseService;
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
     * Get Budget Plans.
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
            Helper::logError('Unable to fetch budget plans', [__CLASS__, __FUNCTION__], $exception);
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

            $totalDays = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;

            if ($today < $startDate) {
                $status = 'upcoming';
                $daysPassed = 0;
                $remainingDays = (int) $totalDays;
                $totalSpent = 0;
                $remainingAmount = $budgetAmount;
                $expectedSpent = 0;
                $dailyAllowance = $budgetAmount / $totalDays;
            } elseif ($today > $endDate) {
                $status = 'ended';
                $daysPassed = (int) $totalDays;
                $remainingDays = 0;
                $expectedSpent = $budgetAmount;
                $totalSpent = 0;
                $remainingAmount = $budgetAmount - $totalSpent;
                $dailyAllowance = 0;
            } else {
                $status = 'active';
                $daysPassed = (strtotime($today) - strtotime($startDate)) / (60 * 60 * 24) + 1;
                $remainingDays = (strtotime($endDate) - strtotime($today)) / (60 * 60 * 24);
                $expectedSpent = ($budgetAmount / $totalDays) * $daysPassed;
                $totalSpent = 0;
                $remainingAmount = $budgetAmount - $totalSpent;
                $dailyAllowance = $remainingDays > 0 ? $remainingAmount / $remainingDays : 0;
            }

            if ($status === 'upcoming') {
                $color = 'blue';
                $statusText = 'Starts in ' . (strtotime($startDate) - strtotime($today)) / (60 * 60 * 24) . ' days';
            } elseif ($status === 'ended') {
                $color = $remainingAmount >= 0 ? 'green' : 'red';
                $statusText = $remainingAmount >= 0 
                    ? 'Completed - ₹' . number_format($remainingAmount, 0) . ' saved' 
                    : 'Over by ₹' . number_format(abs($remainingAmount), 0);
            } else {
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
            Helper::logError('Unable to fetch budget plan status', [__CLASS__, __FUNCTION__], $exception);
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
            Helper::logError('Unable to delete budget plan', [__CLASS__, __FUNCTION__], $exception);
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
     * Get fixed expenses for onboarding.
     */
    public function getExpense()
    {
        try {
            $user = Auth::user();

            $fixedExpenses = ExpenseCategory::where('user_uuid', $user->uuid)
                ->where('expense_period', 'fixed')
                ->get(['uuid', 'category_type', 'default_amount', 'expense_period']);

            return response()->json([
                'expenses' => $fixedExpenses
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to fetch fixed expenses', [__CLASS__, __FUNCTION__], $exception);
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Save/update fixed expenses for onboarding.
     */
    public function saveExpense(Request $request)
    {
        $request->validate([
            'fixed_expenses' => 'required|array',
            'fixed_expenses.*.category_type' => 'required|string',
            'fixed_expenses.*.default_amount' => 'nullable|numeric',
        ]);

        try {
            $this->expenseService->storeBulk($request->fixed_expenses);

            return response()->json([
                'message' => 'Fixed expenses saved successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to save fixed expenses', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return response(['errors' => $exception->getMessage()], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
