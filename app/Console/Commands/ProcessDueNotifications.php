<?php

namespace App\Console\Commands;

use App\Jobs\ProcessNotificationReminderJob;
use App\Models\NotificationSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessDueNotifications extends Command
{
    protected $signature = 'app:process-due-notifications';

    protected $description = 'Find active notification schedules due in the current 1-minute window and dispatch reminder jobs.';

    public function handle(): void
    {
        $now = Carbon::now('Asia/Kolkata');

        // 5-minute lookback window to catch any missed schedules
        $windowStart = $now->copy()->subMinutes(5)->startOfMinute()->format('H:i:s');
        $windowEnd = $now->copy()->endOfMinute()->format('H:i:s');

        $this->info("Processing notifications due between {$windowStart} and {$windowEnd}");

        $dueSchedules = NotificationSchedule::where('is_active', true)
            ->whereBetween('reminder_time', [$windowStart, $windowEnd])
            ->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('No due schedules found.');
            return;
        }

        $this->info("Found {$dueSchedules->count()} due schedule(s). Dispatching jobs...");

        $jobCount = 0;

        foreach ($dueSchedules->chunk(10) as $chunk) {
            $scheduleIds = $chunk->pluck('id')->toArray();

            $job = new ProcessNotificationReminderJob($scheduleIds);
            $job->withHistory(null, ['schedule_ids' => $scheduleIds]);
            dispatch($job);

            $jobCount++;
        }

        $this->info("Dispatched {$jobCount} job(s).");
    }
}
