<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class CronController extends Controller
{
    /**
     * Process due notification reminders via HTTP endpoint.
     * Protected by X-Cron-Secret header.
     */
    public function processNotifications(Request $request)
    {
        $secret = $request->header('X-Cron-Secret');

        if (!$secret || $secret !== config('app.cron_secret')) {
            return Response::json([
                'message' => 'Unauthorized'
            ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
        }

        Artisan::call('app:process-due-notifications');

        return Response::json([
            'message' => 'Notification processing triggered',
            'output' => Artisan::output(),
        ], HttpFoundationResponse::HTTP_OK);
    }
}
