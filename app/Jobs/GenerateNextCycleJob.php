<?php

namespace App\Jobs;

use App\Models\BudgetPlanCycle;
use App\Services\BudgetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateNextCycleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $currentCycle;

    /**
     * Create a new job instance.
     */
    public function __construct(BudgetPlanCycle $currentCycle)
    {
        $this->currentCycle = $currentCycle;
    }

    /**
     * Execute the job.
     */
    public function handle(BudgetService $budgetService)
    {
        try {
            $budgetService->generateNextCycle($this->currentCycle);
        } catch (\Exception $e) {
            Log::error("Failed to generate next cycle for cycle UUID: {$this->currentCycle->uuid}. Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
