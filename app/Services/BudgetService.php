<?php

namespace App\Services;

use App\Models\BudgetPlanCycle;
use App\Models\ExpenseCategory;
use App\Models\ExpenseLog;
use App\Models\Plan;
use Carbon\Carbon;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BudgetService
{
    /**
     * Finds active cycle or creates one if a plan exists for the date.
     */
    public function resolveActiveCycle($user, $date)
    {
        $cycle = BudgetPlanCycle::where('budget_plan_cycles.user_uuid', $user->uuid)
            ->join('plans', 'plans.uuid', '=', 'budget_plan_cycles.plan_uuid')
            ->where('plans.is_active', true)
            ->where('budget_plan_cycles.cycle_start', '<=', $date)
            ->where('budget_plan_cycles.cycle_end', '>=', $date)
            ->where('budget_plan_cycles.status', 'active')
            ->select('budget_plan_cycles.*')
            ->first();

        if ($cycle) {
            return $cycle;
        }

        $activePlan = Plan::where('user_uuid', $user->uuid)
            ->where('type', 'budget')
            ->where('is_active', true)
            ->first();

        return ($activePlan && $date >= $activePlan->start_date)
            ? $this->updateOrCreateCycle($activePlan, Carbon::parse($date), 'active')
            : null;
    }

    /**
     * Maps cycle data to a frontend-friendly summary.
     */
    public function getCycleSummary($cycle)
    {
        $today = now()->toDateString();
        $daysRemaining = (strtotime($cycle->cycle_end) - strtotime($today)) / (60 * 60 * 24);

        $plannedFixedTotal = (float)collect($cycle->meta_data['fixed_snapshot'] ?? [])->sum('amount');
        $safeToSpend = (float)($cycle->budget_amount - $plannedFixedTotal - $cycle->variable_expense_total);
        $safePercentage = $cycle->budget_amount > 0 ? max(0, min(100, ($safeToSpend / $cycle->budget_amount) * 100)) : 0;

        return [
            'cycle_uuid' => $cycle->uuid,
            'plan_uuid' => $cycle->plan_uuid,
            'name' => $cycle->plan->name,
            'total_amount' => (float)$cycle->budget_amount,
            'from_date' => $cycle->cycle_start,
            'to_date' => $cycle->cycle_end,
            'remaining_amount' => (float)$cycle->remaining_amount,
            'remaining_days' => max(0, $daysRemaining),
            'total_spent' => (float)($cycle->fixed_expense_total + $cycle->variable_expense_total),
            'fixed_spent' => (float)$cycle->fixed_expense_total,
            'variable_spent' => (float)$cycle->variable_expense_total,
            'planned_fixed_total' => $plannedFixedTotal,
            'safe_to_spend_amount' => $safeToSpend,
            'safe_to_spend_percentage' => (float)$safePercentage,
        ];
    }

    /**
     * Create the first cycle for a new budget plan.
     */
    public function createInitialCycle(Plan $plan)
    {
        return $this->updateOrCreateCycle($plan, Carbon::parse($plan->start_date), 'active');
    }

    /**
     * Create or Update a specific cycle for a plan.
     */
    public function updateOrCreateCycle(Plan $plan, Carbon $startDate, $status = 'active')
    {
        $type = $plan->meta_data['budget_type'] ?? 'monthly';
        
        $endDate = ($type === 'monthly') 
            ? $startDate->copy()->addMonth()->subDay() 
            : $startDate->copy()->addWeek()->subDay();

        $budgetAmount = $plan->meta_data['amount'] ?? 0;

        $fixedCategories = ExpenseCategory::where('user_uuid', $plan->user_uuid)
            ->where('plan_uuid', $plan->uuid)
            ->where('expense_period', 'fixed')
            ->get();

        $cycle = BudgetPlanCycle::updateOrCreate(
            [
                'user_uuid' => $plan->user_uuid,
                'plan_uuid' => $plan->uuid,
                'cycle_start' => $startDate->toDateString(),
                'cycle_end' => $endDate->toDateString(),
            ],
            [
                'budget_amount' => $budgetAmount,
                'status' => $status,
                'meta_data' => [
                    'type' => $type,
                    'fixed_snapshot' => $fixedCategories->map(fn($e) => [
                        'category_uuid' => $e->uuid,
                        'name' => Helper::deslugifyCategory($e->category_type),
                        'amount' => $e->default_amount
                    ])->toArray()
                ]
            ]
        );

        $this->syncCycleTotals($cycle);
        
        return $cycle;
    }

    /**
     * Calculates the actual spent amount for fixed and variable categories.
     */
    public function calculateActualSpent(BudgetPlanCycle $cycle)
    {
        $totals = ExpenseLog::where('plan_cycle_uuid', $cycle->uuid)
            ->join('expense_categories', 'expense_categories.uuid', '=', 'expense_logs.category_uuid')
            ->selectRaw("
                SUM(CASE WHEN expense_categories.expense_period = 'fixed' THEN expense_logs.amount ELSE 0 END) as fixed_total,
                SUM(CASE WHEN expense_categories.expense_period = 'variable' THEN expense_logs.amount ELSE 0 END) as variable_total
            ")
            ->first();

        return [
            'fixed' => (float)($totals->fixed_total ?? 0),
            'variable' => (float)($totals->variable_total ?? 0)
        ];
    }

    /**
     * Sync totals for the current active cycle based on actual logs.
     */
    public function syncCycleTotals(BudgetPlanCycle $cycle)
    {
        $actual = $this->calculateActualSpent($cycle);
        
        $cycle->update([
            'fixed_expense_total' => $actual['fixed'],
            'variable_expense_total' => $actual['variable'],
            'remaining_amount' => $cycle->budget_amount - $actual['fixed'] - $actual['variable']
        ]);
    }

    /**
     * Recalculate the fixed categories snapshot for the active cycle.
     * Note: This no longer forces the fixed_expense_total column.
     */
    public function recalculateCurrentFixedExpenses($userUuid)
    {
        $activeCycle = BudgetPlanCycle::where('user_uuid', $userUuid)
            ->where('status', 'active')
            ->where('cycle_start', '<=', now()->toDateString())
            ->where('cycle_end', '>=', now()->toDateString())
            ->first();

        if ($activeCycle) {
            $fixedCategories = ExpenseCategory::where('user_uuid', $userUuid)
                ->where('plan_uuid', $activeCycle->plan_uuid)
                ->where('expense_period', 'fixed')
                ->get();

            $metaData = $activeCycle->meta_data ?? [];
            $metaData['fixed_snapshot'] = $fixedCategories->map(fn($e) => [
                'category_uuid' => $e->uuid,
                'name' => Helper::deslugifyCategory($e->category_type),
                'amount' => $e->default_amount
            ])->toArray();

            $activeCycle->update(['meta_data' => $metaData]);
            
            // Re-sync totals to ensure it reflects current logs (in case categories changed type)
            $this->syncCycleTotals($activeCycle);
        }
    }

    /**
     * Mark past active cycles as completed.
     */
    public function completeExpiredCycles()
    {
        $today = now()->toDateString();
        
        BudgetPlanCycle::where('status', 'active')
            ->where('cycle_end', '<', $today)
            ->update(['status' => 'completed']);
    }

    /**
     * Auto-generate the next cycle for a plan.
     */
    public function generateNextCycle(BudgetPlanCycle $currentCycle)
    {
        return DB::transaction(function () use ($currentCycle) {
            $plan = $currentCycle->plan;
            if (!$plan || !$plan->is_active) {
                return null;
            }
            $nextStart = Carbon::parse($currentCycle->cycle_end)->addDay();
            return $this->updateOrCreateCycle($plan, $nextStart, 'active');
        });
    }

    /**
     * Pause active cycle when a plan is deactivated.
     */
    public function pauseActiveCycle($planUuid)
    {
        BudgetPlanCycle::where('plan_uuid', $planUuid)
            ->where('status', 'active')
            ->update(['status' => 'paused']);
    }

    /**
     * Resume a paused cycle when a plan is reactivated.
     */
    public function resumeActiveCycle($planUuid)
    {
        BudgetPlanCycle::where('plan_uuid', $planUuid)
            ->where('status', 'paused')
            ->where('cycle_end', '>=', now()->toDateString())
            ->update(['status' => 'active']);
    }
}
