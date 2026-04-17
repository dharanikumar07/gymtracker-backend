<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class PlanController extends Controller
{
    /**
     * Get plans by type.
     */
    public function getPlans(Request $request)
    {
        try {
            $user = Auth::user();
            $type = $request->query('type');
            $isActive = $request->input('is_active');

            throw_if(!$type, new Exception("Plan type is not found"));

            $query = Plan::where('user_uuid', $user->uuid)
            ->where('type', $type)
            ->orderBy('created_at', 'desc');

            if($isActive) {
                $query->where('is_active', $isActive);
            }

            $plans = $query->get();

            return Response::json([
                'data' => PlanResource::collection($plans)
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to fetch plans', [__CLASS__, __FUNCTION__], $exception);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Save/Update plan using updateOrCreate.
     */
    public function savePlan(PlanRequest $request)
    {
        $request->validate([
            'uuid' => 'nullable|uuid',
            'name' => 'required|string',
            'type' => 'required|string',
            'meta_data' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $user = Auth::user();

            $plan = Plan::updateOrCreate(
                [
                    'uuid' => $request->uuid,
                    'user_uuid' => $user->uuid,
                ],
                [
                    'name' => $request->name,
                    'type' => $request->type,
                    'meta_data' => $request->meta_data ?? [],
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            return Response::json([
                'message' => 'Plan saved successfully',
                'data' => new PlanResource($plan)
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to save plan', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deletePlan($uuid)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $plan = Plan::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->first();

            throw_if(!$plan, new Exception('Plan not found'));

            $plan->delete();

            DB::commit();

            return Response::json([
                'message' => 'Plan deleted successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to delete plan', [__CLASS__, __FUNCTION__], $exception);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
