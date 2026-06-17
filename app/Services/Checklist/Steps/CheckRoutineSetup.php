<?php

namespace App\Services\Checklist\Steps;

use App\Models\PhysicalActivitySlot;
use App\Services\Checklist\ChecklistContext;
use Closure;

class CheckRoutineSetup
{
    public function handle(ChecklistContext $context, Closure $next): mixed
    {
        $hasSlots = PhysicalActivitySlot::where('user_uuid', $context->user->uuid)->exists();

        $context->addStep(
            key: 'routine',
            label: 'Set up your routine',
            enabled: $hasSlots,
            url: '/track-progress/routine',
            description: 'Add exercises to your weekly routine',
        );

        return $next($context);
    }
}
