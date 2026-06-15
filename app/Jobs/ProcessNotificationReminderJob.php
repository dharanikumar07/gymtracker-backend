<?php

namespace App\Jobs;

use App\Models\ExpenseLog;
use App\Models\NotificationLog;
use App\Models\NotificationSchedule;
use App\Models\PhysicalActivityTracker;
use App\Models\UserDeviceToken;
use App\Services\FcmService;
use App\Traits\HasJobHistory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNotificationReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasJobHistory;

    public int $tries = 3;

    protected array $scheduleIds;

    public function __construct(array $scheduleIds)
    {
        $this->scheduleIds = $scheduleIds;
    }

    public function handle(FcmService $fcmService): void
    {
        $this->markHistoryRunning();

        try {
            $today = Carbon::today()->toDateString();
            $schedules = NotificationSchedule::whereIn('id', $this->scheduleIds)
                ->where('is_active', true)
                ->get();

            foreach ($schedules as $schedule) {
                try {
                    $this->processSchedule($schedule, $today, $fcmService);
                } catch (\Exception $e) {
                    Log::error("Notification job failed for schedule {$schedule->uuid}", [
                        'error' => $e->getMessage(),
                    ]);

                    NotificationLog::updateOrCreate(
                        [
                            'user_uuid' => $schedule->user_uuid,
                            'module' => $schedule->module,
                            'notification_date' => $today,
                            'notification_time' => $schedule->reminder_time,
                        ],
                        [
                            'status' => 'failed',
                            'failure_reason' => $e->getMessage(),
                        ]
                    );
                }
            }

            $this->markHistoryCompleted();
        } catch (\Exception $e) {
            $this->markHistoryFailed($e->getMessage());
            throw $e;
        }
    }

    protected function processSchedule(NotificationSchedule $schedule, string $today, FcmService $fcmService): void
    {
        $existingLog = NotificationLog::where('user_uuid', $schedule->user_uuid)
            ->where('module', $schedule->module)
            ->where('notification_date', $today)
            ->where('notification_time', $schedule->reminder_time)
            ->where('status', 'completed')
            ->first();

        if ($existingLog) {
            return;
        }

        $hasLoggedToday = $this->hasUserLoggedToday($schedule->user_uuid, $schedule->module, $today);

        if ($hasLoggedToday) {
            NotificationLog::updateOrCreate(
                [
                    'user_uuid' => $schedule->user_uuid,
                    'module' => $schedule->module,
                    'notification_date' => $today,
                    'notification_time' => $schedule->reminder_time,
                ],
                [
                    'status' => 'skipped',
                ]
            );
            return;
        }

        $tokens = UserDeviceToken::where('user_uuid', $schedule->user_uuid)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            NotificationLog::updateOrCreate(
                [
                    'user_uuid' => $schedule->user_uuid,
                    'module' => $schedule->module,
                    'notification_date' => $today,
                    'notification_time' => $schedule->reminder_time,
                ],
                [
                    'status' => 'failed',
                    'failure_reason' => 'No active device tokens found',
                ]
            );
            return;
        }

        $content = FcmService::getNotificationContent($schedule->module);
        $result = $fcmService->sendToTokens($tokens, $content['title'], $content['body'], [
            'module' => $schedule->module,
            'type' => 'reminder',
        ]);

        if ($result['success'] > 0) {
            NotificationLog::updateOrCreate(
                [
                    'user_uuid' => $schedule->user_uuid,
                    'module' => $schedule->module,
                    'notification_date' => $today,
                    'notification_time' => $schedule->reminder_time,
                ],
                [
                    'status' => 'completed',
                    'actual_notification_time_sended' => Carbon::now(),
                ]
            );
        } else {
            NotificationLog::updateOrCreate(
                [
                    'user_uuid' => $schedule->user_uuid,
                    'module' => $schedule->module,
                    'notification_date' => $today,
                    'notification_time' => $schedule->reminder_time,
                ],
                [
                    'status' => 'failed',
                    'failure_reason' => implode('; ', $result['errors']),
                ]
            );
        }
    }

    protected function hasUserLoggedToday(string $userUuid, string $module, string $today): bool
    {
        return match ($module) {
            'workout' => PhysicalActivityTracker::where('user_uuid', $userUuid)
                ->where('activity_date', $today)
                ->exists(),

            'expense' => ExpenseLog::where('user_uuid', $userUuid)
                ->where('expense_date', $today)
                ->exists(),

            default => false,
        };
    }
}
