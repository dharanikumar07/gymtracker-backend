<?php

namespace App\Jobs;

use App\Models\BudgetPlanCycle;
use App\Http\Helpers\Helper;
use App\Services\BudgetService;
use App\Traits\HasJobHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateNextCycleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasJobHistory;

    protected $currentCycle;

    public function __construct(BudgetPlanCycle $currentCycle)
    {
        $this->currentCycle = $currentCycle;
    }

    public function failed(\Throwable $exception): void
    {
        $this->markHistoryFailed($exception->getMessage());
        Helper::logError('GenerateNextCycleJob failed', [__CLASS__, __FUNCTION__], $exception, [
            'cycle_uuid' => $this->currentCycle->uuid ?? null,
        ]);
    }

    public function handle(BudgetService $budgetService)
    {
        $this->markHistoryRunning();

        try {
            $budgetService->generateNextCycle($this->currentCycle);
            $this->markHistoryCompleted();
        } catch (\Exception $e) {
            Log::error("Failed to generate next cycle for cycle UUID: {$this->currentCycle->uuid}. Error: {$e->getMessage()}");
            $this->markHistoryFailed($e->getMessage());
            throw $e;
        }
    }
}
