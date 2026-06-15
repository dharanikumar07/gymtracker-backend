<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Models\NotificationSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationScheduleSyncService
{
    /**
     * Modules supported for notification reminders.
     */
    const MODULES = ['workout', 'expense'];

    /**
     * Sync notification schedules from the saved settings data.
     *
     * - If a module is disabled, mark all its schedules as inactive.
     * - If a module is enabled, mark all old schedules inactive first,
     *   then updateOrCreate for each selected time and set is_active = true.
     *
     * @param string $userUuid
     * @param array  $settingsData  e.g. ['workout' => [...], 'expense' => [...]]
     */
    public function sync(string $userUuid, array $settingsData): void
    {
        DB::beginTransaction();

        try {
            foreach (self::MODULES as $module) {
                $moduleData = $settingsData[$module] ?? [];
                $isEnabled = (bool) ($moduleData['is_enabled'] ?? false);
                $times = $moduleData['times'] ?? [];
                $reminderCount = (int) ($moduleData['reminder_count'] ?? count($times));

                if (!$isEnabled) {
                    NotificationSchedule::where('user_uuid', $userUuid)
                        ->where('module', $module)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);

                    continue;
                }

                NotificationSchedule::where('user_uuid', $userUuid)
                    ->where('module', $module)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                foreach ($times as $index => $time) {
                    NotificationSchedule::updateOrCreate(
                        [
                            'user_uuid' => $userUuid,
                            'module' => $module,
                            'reminder_count' => $index + 1,
                        ],
                        [
                            'reminder_time' => $time,
                            'is_active' => true,
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Helper::logError(
                'Error occurred in saveSettings',
                [__CLASS__, __FUNCTION__],
                $e,
                $e->getMessage()
            );
        }
    }
}
