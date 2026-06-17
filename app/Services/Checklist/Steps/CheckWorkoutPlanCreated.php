<?php

namespace App\Services\Checklist\Steps;

use App\Models\Plan;
use App\Services\Checklist\ChecklistContext;
use Closure;

class CheckWorkoutPlanCreated
{
    public function handle(ChecklistContext $context, Closure $next): mixed
    {
        $hasPlan = Plan::where('user_uuid', $context->user->uuid)
            ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
            ->where('is_active', true)
            ->exists();

        $context->addStep(
            key: 'workout_plan',
            label: 'Create a workout plan',
            enabled: $hasPlan,
            url: '/track-progress/routine',
            description: 'Set up your fitness plan to track workouts',
        );

        return $next($context);
    }
}
