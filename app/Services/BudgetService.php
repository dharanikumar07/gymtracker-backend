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

        $fixedExpenses = ExpenseCategory::where('user_uuid', $plan->user_uuid)
            ->where('plan_uuid', $plan->uuid)
            ->where('expense_period', 'fixed')
            ->get();

        $fixedTotal = $fixedExpenses->sum('default_amount');
        
        $existing = BudgetPlanCycle::where('user_uuid', $plan->user_uuid)
            ->where('plan_uuid', $plan->uuid)
            ->where('cycle_start', $startDate->toDateString())
            ->first();

        $variableTotal = $existing ? $existing->variable_expense_total : 0;

        return BudgetPlanCycle::updateOrCreate(
            [
                'user_uuid' => $plan->user_uuid,
                'plan_uuid' => $plan->uuid,
                'cycle_start' => $startDate->toDateString(),
                'cycle_end' => $endDate->toDateString(),
            ],
            [
                'budget_amount' => $budgetAmount,
                'fixed_expense_total' => $fixedTotal,
                'variable_expense_total' => $variableTotal,
                'remaining_amount' => $budgetAmount - $fixedTotal - $variableTotal,
                'status' => $status,
                'meta_data' => [
                    'type' => $type,
                    'fixed_snapshot' => $fixedExpenses->map(fn($e) => [
                        'category_uuid' => $e->uuid,
                        'name' => Helper::deslugifyCategory($e->category_type),
                        'amount' => $e->default_amount
                    ])->toArray()
                ]
            ]
        );
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

    /**
     * Sync totals for the current active cycle (e.g. after adding an expense).
     */
    public function syncCycleTotals(BudgetPlanCycle $cycle)
    {
        $variableTotal = ExpenseLog::where('plan_cycle_uuid', $cycle->uuid)->sum('amount');
        
        $cycle->update([
            'variable_expense_total' => $variableTotal,
            'remaining_amount' => $cycle->budget_amount - $cycle->fixed_expense_total - $variableTotal
        ]);
    }

    /**
     * Recalculate fixed expenses for the CURRENT active cycle only.
     */
    public function recalculateCurrentFixedExpenses($userUuid)
    {
        $activeCycle = BudgetPlanCycle::where('user_uuid', $userUuid)
            ->where('status', 'active')
            ->where('cycle_start', '<=', now()->toDateString())
            ->where('cycle_end', '>=', now()->toDateString())
            ->first();

        if ($activeCycle) {
            $fixedExpenses = ExpenseCategory::where('user_uuid', $userUuid)
                ->where('plan_uuid', $activeCycle->plan_uuid)
                ->where('expense_period', 'fixed')
                ->get();

            $fixedTotal = $fixedExpenses->sum('default_amount');
            
            $metaData = $activeCycle->meta_data ?? [];
            $metaData['fixed_snapshot'] = $fixedExpenses->map(fn($e) => [
                'category_uuid' => $e->uuid,
                'name' => Helper::deslugifyCategory($e->category_type),
                'amount' => $e->default_amount
            ])->toArray();

            $activeCycle->update([
                'fixed_expense_total' => $fixedTotal,
                'remaining_amount' => $activeCycle->budget_amount - $fixedTotal - $activeCycle->variable_expense_total,
                'meta_data' => $metaData
            ]);
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
}
