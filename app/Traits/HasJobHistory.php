<?php

namespace App\Traits;

use App\Models\JobHistory;
use Carbon\Carbon;

/**
 * Trait for queued jobs to track execution in job_histories table.
 *
 * Usage in commands:
 *   $job = new SomeJob($args);
 *   $job->withHistory(userUuid: null, payload: ['key' => 'value']);
 *   dispatch($job);
 *
 * Usage in jobs:
 *   public function handle(): void {
 *       $this->markHistoryRunning();
 *       try {
 *           // ... job logic
 *           $this->markHistoryCompleted();
 *       } catch (\Exception $e) {
 *           $this->markHistoryFailed($e->getMessage());
 *           throw $e;
 *       }
 *   }
 */
trait HasJobHistory
{
    public ?string $jobHistoryUuid = null;

    /**
     * Create a job_histories entry (status=pending) and attach its UUID to this job.
     * Call this before dispatching.
     */
    public function withHistory(?string $userUuid = null, ?array $payload = null): static
    {
        $history = JobHistory::create([
            'user_uuid' => $userUuid,
            'job_name' => class_basename(static::class),
            'queue' => $this->queue ?? 'default',
            'status' => 'pending',
            'payload' => $payload,
        ]);

        $this->jobHistoryUuid = $history->uuid;

        return $this;
    }

    /**
     * Mark the job as running. Call at the start of handle().
     */
    protected function markHistoryRunning(): void
    {
        if (!$this->jobHistoryUuid) return;

        JobHistory::where('uuid', $this->jobHistoryUuid)->update([
            'status' => 'running',
            'started_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark the job as completed. Call at the end of handle() on success.
     */
    protected function markHistoryCompleted(): void
    {
        if (!$this->jobHistoryUuid) return;

        JobHistory::where('uuid', $this->jobHistoryUuid)->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark the job as failed. Call in the catch block of handle().
     */
    protected function markHistoryFailed(string $reason): void
    {
        if (!$this->jobHistoryUuid) return;

        JobHistory::where('uuid', $this->jobHistoryUuid)->update([
            'status' => 'failed',
            'failure_reason' => mb_substr($reason, 0, 2000),
            'completed_at' => Carbon::now(),
        ]);
    }
}
