<?php

namespace App\Console\Commands;

use App\Models\BudgetPlanCycle;
use App\Jobs\GenerateNextCycleJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateBudgetCycles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-budget-cycles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find active budget cycles ending tomorrow and generate the next cycle.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $budgetService = app(\App\Services\BudgetService::class);
        $tomorrow = Carbon::tomorrow()->toDateString();

        $this->info("Cleaning up expired cycles...");
        $budgetService->completeExpiredCycles();

        $this->info("Scanning for active cycles ending on or before {$tomorrow}...");

        // We check for '<=' to pick up any cycles that might have been missed 
        // if the cron failed on previous days.
        BudgetPlanCycle::where('cycle_end', '<=', $tomorrow)
            ->where('status', 'active')
            ->chunkById(1000, function ($cycles) {
                foreach ($cycles as $cycle) {
                    $this->line("Dispatching generation job for Cycle UUID: {$cycle->uuid}");
                    GenerateNextCycleJob::dispatch($cycle);
                }
            });

        $this->info("Dispatched generation jobs for all eligible cycles.");
    }
}
