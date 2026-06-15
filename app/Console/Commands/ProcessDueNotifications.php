<?php

namespace App\Console\Commands;

use App\Jobs\ProcessNotificationReminderJob;
use App\Models\NotificationSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessDueNotifications extends Command
{
    protected $signature = 'app:process-due-notifications';

    protected $description = 'Find active notification schedules due in the current 1-minute window and dispatch reminder jobs.';

    public function handle(): void
    {
        $now = Carbon::now('Asia/Kolkata');

        // 1-minute window: from the start of the current minute to end of current minute
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

        // Chunk by 10 and create batch jobs
        $jobs = [];

        foreach ($dueSchedules->chunk(10) as $chunk) {
            $jobs[] = new ProcessNotificationReminderJob($chunk->pluck('id')->toArray());
        }

        Bus::batch($jobs)
            ->name('Process Due Notification Reminders - ' . $now->toDateTimeString())
            ->allowFailures()
            ->dispatch();

        $this->info('Dispatched ' . count($jobs) . ' job(s) in batch.');
    }
}
