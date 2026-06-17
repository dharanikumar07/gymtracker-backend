<?php

namespace App\Services\Checklist\Steps;

use App\Models\Plan;
use App\Services\Checklist\ChecklistContext;
use Closure;

class CheckBudgetPlanCreated
{
    public function handle(ChecklistContext $context, Closure $next): mixed
    {
        $hasPlan = Plan::where('user_uuid', $context->user->uuid)
            ->where('type', 'budget')
            ->where('is_active', true)
            ->exists();

        $context->addStep(
            key: 'budget_plan',
            label: 'Create a budget plan',
            enabled: $hasPlan,
            url: '/track-expense/setup',
            description: 'Set up your monthly budget to track expenses',
        );

        return $next($context);
    }
}
