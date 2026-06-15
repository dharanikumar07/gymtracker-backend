<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class CronController extends Controller
{
    /**
     * Wake up the Render instance.
     * No secret required — this is just a health check.
     */
    public function ping()
    {
        return Response::json([
            'status' => 'ok',
            'timestamp' => now()->toDateTimeString(),
        ], HttpFoundationResponse::HTTP_OK);
    }

    /**
     * Cron: runs every 1 minute.
     * Executes the notification reminder command.
     */
    public function min()
    {
        try {
            Artisan::call('app:process-due-notifications');

            return Response::json([
                'message' => 'Notification cron executed',
                'output' => Artisan::output(),
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Cron min failed', [__CLASS__, __FUNCTION__], $e, []);

            return Response::json([
                'message' => 'Cron min failed',
                'error' => $e->getMessage(),
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cron: runs twice per day.
     * Executes the budget cycle generation command.
     */
    public function twicePerDay()
    {
        try {
            Artisan::call('app:generate-budget-cycles');

            return Response::json([
                'message' => 'Budget cycle cron executed',
                'output' => Artisan::output(),
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Cron twice-per-day failed', [__CLASS__, __FUNCTION__], $e, []);

            return Response::json([
                'message' => 'Cron twice-per-day failed',
                'error' => $e->getMessage(),
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Process up to 5 queued jobs from Redis, max 25 seconds.
     * Called by cron-job.org as a separate request to avoid timeouts.
     */
    public function processJobs()
    {
        try {
            $startTime = microtime(true);

            Artisan::call('queue:work', [
                'connection' => 'redis',
                '--queue' => 'default',
                '--max-jobs' => 5,
                '--max-time' => 25,
                '--stop-when-empty' => true,
            ]);

            $elapsed = round(microtime(true) - $startTime, 2);

            return Response::json([
                'message' => 'Job processing completed',
                'elapsed_seconds' => $elapsed,
                'output' => Artisan::output(),
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Cron process-jobs failed', [__CLASS__, __FUNCTION__], $e, []);

            return Response::json([
                'message' => 'Job processing failed',
                'error' => $e->getMessage(),
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
