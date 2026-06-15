<?php

namespace App\Console\Commands;

use App\Models\BudgetPlanCycle;
use App\Jobs\GenerateNextCycleJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateBudgetCycles extends Command
{
    protected $signature = 'app:generate-budget-cycles';

    protected $description = 'Find active budget cycles ending tomorrow and generate the next cycle.';

    public function handle()
    {
        $budgetService = app(\App\Services\BudgetService::class);
        $tomorrow = Carbon::tomorrow()->toDateString();

        $this->info("Cleaning up expired cycles...");
        $budgetService->completeExpiredCycles();

        $this->info("Scanning for active cycles ending on or before {$tomorrow}...");

        BudgetPlanCycle::where('cycle_end', '<=', $tomorrow)
            ->where('status', 'active')
            ->chunkById(1000, function ($cycles) {
                foreach ($cycles as $cycle) {
                    $this->line("Dispatching generation job for Cycle UUID: {$cycle->uuid}");

                    $job = new GenerateNextCycleJob($cycle);
                    $job->withHistory($cycle->user_uuid, ['cycle_uuid' => $cycle->uuid]);
                    dispatch($job);
                }
            });

        $this->info("Dispatched generation jobs for all eligible cycles.");
    }
}
