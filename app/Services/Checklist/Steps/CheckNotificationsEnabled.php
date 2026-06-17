<?php

namespace App\Services\Checklist\Steps;

use App\Models\NotificationSchedule;
use App\Services\Checklist\ChecklistContext;
use Closure;

class CheckNotificationsEnabled
{
    public function handle(ChecklistContext $context, Closure $next): mixed
    {
        $hasActive = NotificationSchedule::where('user_uuid', $context->user->uuid)
            ->where('is_active', true)
            ->exists();

        $context->addStep(
            key: 'notifications',
            label: 'Enable notifications',
            enabled: $hasActive,
            url: '/settings/notifications',
            description: 'Turn on workout and expense reminders',
        );

        return $next($context);
    }
}
