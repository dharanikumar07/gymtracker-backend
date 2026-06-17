<?php

namespace App\Services\Checklist\Steps;

use App\Services\Checklist\ChecklistContext;
use Closure;

class CheckProfileCompleted
{
    public function handle(ChecklistContext $context, Closure $next): mixed
    {
        $fitnessData = $context->user->user_fitness_data ?? [];

        $hasProfile = !empty($context->user->name)
            && !empty($fitnessData['age'])
            && !empty($fitnessData['height'])
            && !empty($fitnessData['weight']);

        $context->addStep(
            key: 'profile',
            label: 'Complete your profile',
            enabled: $hasProfile,
            url: '/settings/profile',
            description: 'Add your personal details and fitness data',
        );

        return $next($context);
    }
}
