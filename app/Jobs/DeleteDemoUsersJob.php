<?php

namespace App\Jobs;

use App\Http\Helpers\Helper;
use App\Models\User;
use App\Traits\HasJobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class DeleteDemoUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasJobHistory;

    public int $tries = 3;

    protected array $userUuids;

    public function __construct(array $userUuids)
    {
        $this->userUuids = $userUuids;
    }

    public function handle(): void
    {
        $this->markHistoryRunning();

        try {
            $users = User::whereIn('uuid', $this->userUuids)
                ->where('is_demo', true)
                ->get();

            foreach ($users as $user) {
                try {
                    // Revoke all tokens
                    PersonalAccessToken::where('tokenable_id', $user->id)
                        ->where('tokenable_type', User::class)
                        ->delete();

                    // Soft-delete the user
                    $user->delete();

                    Log::info("Demo user soft-deleted: {$user->uuid} ({$user->email})");
                } catch (\Exception $e) {
                    Log::error("Failed to delete demo user {$user->uuid}: {$e->getMessage()}");
                }
            }

            $this->markHistoryCompleted();
        } catch (\Exception $e) {
            $this->markHistoryFailed($e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->markHistoryFailed($exception->getMessage());
        Helper::logError('DeleteDemoUsersJob failed', [__CLASS__, __FUNCTION__], $exception, [
            'user_uuids' => $this->userUuids,
        ]);
    }
}
