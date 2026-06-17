<?php

namespace App\Services\Checklist\Steps;

use App\Models\ExpenseCategory;
use App\Services\Checklist\ChecklistContext;
use Closure;

class CheckExpenseCategoriesAdded
{
    public function handle(ChecklistContext $context, Closure $next): mixed
    {
        $hasCategories = ExpenseCategory::where('user_uuid', $context->user->uuid)->exists();

        $context->addStep(
            key: 'expense_categories',
            label: 'Add expense categories',
            enabled: $hasCategories,
            url: '/track-expense/setup',
            description: 'Define your fixed and variable expense categories',
        );

        return $next($context);
    }
}
