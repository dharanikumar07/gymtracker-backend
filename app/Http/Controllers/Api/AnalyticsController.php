<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsOverviewService;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AnalyticsController extends Controller
{
    protected $overviewService;

    public function __construct(AnalyticsOverviewService $overviewService)
    {
        $this->overviewService = $overviewService;
    }

    /**
     * Get analytics overview.
     */
    public function overview(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'date' => $request->input('date', now()->toDateString())
            ];

            $data = $this->overviewService->getOverview($user, $params);
            
            return Response::json([
                'data' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch analytics overview', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
