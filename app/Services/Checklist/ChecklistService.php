<?php

namespace App\Services\Checklist;

use App\Models\User;
use App\Services\Checklist\Steps\CheckProfileCompleted;
use App\Services\Checklist\Steps\CheckWorkoutPlanCreated;
use App\Services\Checklist\Steps\CheckRoutineSetup;
use App\Services\Checklist\Steps\CheckBudgetPlanCreated;
use App\Services\Checklist\Steps\CheckExpenseCategoriesAdded;
use App\Services\Checklist\Steps\CheckNotificationsEnabled;
use Illuminate\Pipeline\Pipeline;

class ChecklistService
{
    public function getChecklistSteps(User $user): array
    {
        $context = new ChecklistContext($user);

        $steps = [
            CheckProfileCompleted::class,
            CheckWorkoutPlanCreated::class,
            CheckRoutineSetup::class,
            CheckBudgetPlanCreated::class,
            CheckExpenseCategoriesAdded::class,
            CheckNotificationsEnabled::class,
        ];

        $context = app(Pipeline::class)
            ->send($context)
            ->through($steps)
            ->thenReturn();

        $completed = collect($context->steps)->sum(fn($s) => $s['enabled'] ? 1 : 0);

        return [
            'total_steps' => count($context->steps),
            'completed_steps' => $completed,
            'steps' => $context->steps,
        ];
    }
}
