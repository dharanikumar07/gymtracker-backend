<?php

namespace App\Console\Commands;

use App\Jobs\DeleteDemoUsersJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupDemoUsers extends Command
{
    protected $signature = 'app:cleanup-demo-users';

    protected $description = 'Find expired demo users and dispatch cleanup jobs (10 users per job).';

    public function handle(): void
    {
        $expiredDemoUsers = User::where('is_demo', true)
            ->where('demo_expires_at', '<', Carbon::now())
            ->whereNull('deleted_at')
            ->pluck('uuid');

        if ($expiredDemoUsers->isEmpty()) {
            $this->info('No expired demo users found.');
            return;
        }

        $this->info("Found {$expiredDemoUsers->count()} expired demo user(s). Dispatching jobs...");

        $jobCount = 0;

        foreach ($expiredDemoUsers->chunk(10) as $chunk) {
            $userUuids = $chunk->values()->toArray();

            $job = new DeleteDemoUsersJob($userUuids);
            $job->withHistory(null, ['user_uuids' => $userUuids]);
            dispatch($job);

            $jobCount++;
        }

        $this->info("Dispatched {$jobCount} job(s).");
    }
}
