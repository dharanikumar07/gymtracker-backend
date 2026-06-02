<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsOverviewService;
use App\Services\AnalyticsWorkoutService;
use App\Services\AnalyticsExpenseService;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AnalyticsController extends Controller
{
    protected $overviewService;
    protected $workoutService;
    protected $expenseService;

    public function __construct(
        AnalyticsOverviewService $overviewService,
        AnalyticsWorkoutService $workoutService,
        AnalyticsExpenseService $expenseService
    ) {
        $this->overviewService = $overviewService;
        $this->workoutService = $workoutService;
        $this->expenseService = $expenseService;
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

    /**
     * Get workout logs for analytics.
     */
    public function workoutLog(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            if (!$params['start_date']) {
                return Response::json(['message' => 'start_date is required'], 400);
            }

            $logs = $this->workoutService->getWorkoutLogs($user, $params);

            return Response::json([
                'data' => $logs
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch workout logs analytics', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available exercises for the user.
     */
    public function getAvailableExercises(Request $request)
    {
        try {
            $user = $request->user();
            $exercises = $this->workoutService->getAvailableExercises($user);

            return Response::json([
                'data' => $exercises
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch available exercises', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get workout insights summary.
     */
    public function workoutInsights(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            if (!$params['start_date']) {
                return Response::json(['message' => 'start_date is required'], 400);
            }

            $summary = $this->workoutService->getWorkoutSummary($user, $params);

            return Response::json([
                'data' => [
                    'summary' => $summary,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch workout insights', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get muscle distribution trend data.
     */
    public function muscleDistribution(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'period_type' => $request->input('period_type', 'month'),
                'lookback' => $request->input('lookback', '4'),
            ];

            $data = $this->workoutService->getMuscleDistribution($user, $params);

            return Response::json([
                'data' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch muscle distribution data', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get progressive overload data.
     */
    public function progressiveOverload(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'exercise_uuid' => $request->input('exercise_uuid'),
                'period_type' => $request->input('period_type', 'month'),
                'lookback' => $request->input('lookback', '4'),
            ];

            if (!$params['exercise_uuid']) {
                return Response::json(['message' => 'exercise_uuid is required'], 400);
            }

            $data = $this->workoutService->getProgressiveOverload($user, $params);

            return Response::json([
                'data' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch progressive overload data', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * EXPENSE ANALYTICS ENDPOINTS
     */

    public function getAvailableCategories(Request $request)
    {
        try {
            $user = $request->user();
            $categories = $this->expenseService->getAvailableCategories($user);

            return Response::json([
                'data' => $categories
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch available categories', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function expenseLog(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            if (!$params['start_date']) {
                return Response::json(['message' => 'start_date is required'], 400);
            }

            $logs = $this->expenseService->getExpenseLogs($user, $params);

            return Response::json([
                'data' => $logs
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch expense logs analytics', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function expenseInsights(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            if (!$params['start_date']) {
                return Response::json(['message' => 'start_date is required'], 400);
            }

            $summary = $this->expenseService->getExpenseSummary($user, $params);

            return Response::json([
                'data' => [
                    'summary' => $summary,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch expense insights', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function expenseTrend(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'period_type' => $request->input('period_type', 'month'),
                'lookback' => $request->input('lookback', '4'),
                'category_uuid' => $request->input('category_uuid'),
            ];

            $data = $this->expenseService->getExpenseTrend($user, $params);

            return Response::json([
                'data' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch expense trend data', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function expenseDistribution(Request $request)
    {
        try {
            $user = $request->user();
            $params = [
                'period_type' => $request->input('period_type', 'month'),
                'lookback' => $request->input('lookback', '4'),
            ];

            $data = $this->expenseService->getExpenseDistribution($user, $params);

            return Response::json([
                'data' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError('Unable to fetch expense distribution data', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
