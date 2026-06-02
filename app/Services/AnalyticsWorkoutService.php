<?php

namespace App\Services;

use App\Models\PhysicalActivityTracker;
use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AnalyticsWorkoutService
{
    private function getBaseQuery($user, array $params)
    {
        $query = PhysicalActivityTracker::with(['slot', 'plan'])
            ->where('user_uuid', $user->uuid);

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $query->whereBetween('activity_date', [$params['start_date'], $params['end_date']]);
        } elseif (!empty($params['start_date'])) {
            $query->where('activity_date', $params['start_date']);
        }

        return $query;
    }

    public function getWorkoutLogs($user, array $params)
    {
        $startDate = Carbon::parse($params['start_date']);
        $endDate = !empty($params['end_date']) ? Carbon::parse($params['end_date']) : $startDate->copy();
        
        $logs = $this->getBaseQuery($user, $params)
            ->orderBy('activity_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $plan = Plan::where('user_uuid', $user->uuid)
            ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
            ->where('is_active', true)
            ->first();

        $allSlots = [];
        if ($plan) {
            $allSlots = PhysicalActivitySlot::where('plan_uuid', $plan->uuid)
                ->where('user_uuid', $user->uuid)
                ->orderBy('exercise_order')
                ->get()
                ->groupBy('day');
        }

        $groupedData = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = strtolower($date->format('D'));
            $dayWorkouts = [];
            
            $dateLogs = $logs->where('activity_date', $date->startOfDay())->values();
            $loggedSlotUuids = $dateLogs->pluck('slot_uuid')->toArray();

            foreach ($dateLogs as $log) {
                $dayWorkouts[] = [
                    'workout_name' => $log->slot->exercise_name ?? 'Unknown',
                    'metrics_data' => $log->metrics_data,
                    'day' => $log->day,
                    'metrics_type' => $log->slot->metrics_type ?? null,
                    'status' => $log->status,
                    'reason' => $log->reason,
                    'type' => 'logged',
                    'slot_uuid' => $log->slot_uuid
                ];
            }

            if (isset($allSlots[$dayOfWeek])) {
                foreach ($allSlots[$dayOfWeek] as $slot) {
                    if (!in_array($slot->uuid, $loggedSlotUuids)) {
                        $dayWorkouts[] = [
                            'workout_name' => $slot->exercise_name,
                            'metrics_data' => $slot->metrics_data,
                            'day' => $slot->day,
                            'metrics_type' => $slot->metrics_type,
                            'status' => 'pending',
                            'reason' => null,
                            'type' => 'template',
                            'slot_uuid' => $slot->uuid
                        ];
                    }
                }
            }

            if (!empty($dayWorkouts)) {
                $groupedData[$dateStr] = $dayWorkouts;
            }
        }

        return $groupedData;
    }

    public function getAvailableExercises($user)
    {
        return PhysicalActivitySlot::where('user_uuid', $user->uuid)
            ->select('uuid', 'exercise_name', 'meta_data')
            ->get()
            ->unique('exercise_name')
            ->map(function ($slot) {
                $targetMuscles = $slot->meta_data['target_muscles'] ?? [];
                return [
                    'uuid' => $slot->uuid,
                    'name' => $slot->exercise_name,
                    'targeted_muscle' => count($targetMuscles) > 0 ? $targetMuscles[0] : 'General'
                ];
            })->values();
    }

    public function getWorkoutSummary($user, array $params)
    {
        $baseQuery = $this->getBaseQuery($user, $params);
        $allLogs = (clone $baseQuery)->get();

        $totalVolume = 0;
        $totalReps = 0;
        $personalBest = ['weight' => 0, 'exercise' => 'N/A'];

        foreach ($allLogs as $log) {
            $totalVolume += $this->calculateLogVolume($log);
            
            $sets = $log->metrics_data['sets'] ?? [];
            foreach ($sets as $set) {
                $reps = (int) ($set['reps'] ?? 0);
                $weight = (float) ($set['weight'] ?? 0);
                $totalReps += $reps;

                if ($weight > $personalBest['weight']) {
                    $personalBest = [
                        'weight' => $weight,
                        'exercise' => $log->slot->exercise_name ?? 'Unknown'
                    ];
                }
            }
        }

        $stats = (clone $baseQuery)
            ->select(
                DB::raw('count(*) as total_sessions'),
                DB::raw('count(distinct activity_date) as active_days')
            )
            ->first();

        return [
            'total_sessions' => (int) ($stats->total_sessions ?? 0),
            'active_days' => (int) ($stats->active_days ?? 0),
            'total_volume' => $totalVolume,
            'total_reps' => $totalReps,
            'personal_best' => $personalBest,
            'avg_volume_per_session' => $stats->total_sessions > 0 ? round($totalVolume / $stats->total_sessions, 2) : 0,
            'completion_rate' => 100
        ];
    }

    /**
     * Get Muscle Distribution in Time-Series Format (Array of Periods).
     */
    public function getMuscleDistribution($user, array $params)
    {
        $periodType = $params['period_type'] ?? 'month';
        $lookback = $params['lookback'] ?? '4';
        
        $now = Carbon::now();
        $periods = [];
        $count = $this->getLookbackCount($user, $lookback, $periodType);

        for ($i = $count - 1; $i >= 0; $i--) {
            if ($periodType === 'month') {
                $target = $now->copy()->subMonths($i);
                $periods[] = [
                    'label' => $target->format('M Y'),
                    'start' => $target->copy()->startOfMonth(),
                    'end' => $target->copy()->endOfMonth(),
                    'period' => $target->format('Y-m')
                ];
            } elseif ($periodType === 'week') {
                $target = $now->copy()->subWeeks($i);
                $periods[] = [
                    'label' => 'W' . $target->weekOfYear . ' ' . $target->format('y'),
                    'start' => $target->copy()->startOfWeek(),
                    'end' => $target->copy()->endOfWeek(),
                    'period' => $target->copy()->startOfWeek()->format('Y-m-d')
                ];
            } else { // day
                $start = $now->copy()->subDays($i)->startOfDay();
                $end = $start->copy()->endOfDay();
                $periods[] = [
                    'label' => $start->format('d M'),
                    'start' => $start,
                    'end' => $end,
                    'period' => $start->toDateString()
                ];
            }
        }

        $result = [];
        foreach ($periods as $p) {
            $logs = PhysicalActivityTracker::with('slot')
                ->where('user_uuid', $user->uuid)
                ->whereBetween('activity_date', [$p['start'], $p['end']])
                ->get();

            $muscleVolume = [];
            $totalPeriodVolume = 0;

            foreach ($logs as $log) {
                $v = $this->calculateLogVolume($log);
                $totalPeriodVolume += $v;
                
                $muscles = $log->slot->meta_data['target_muscles'] ?? ['General'];
                foreach ($muscles as $m) {
                    $m = ucfirst(strtolower($m));
                    $muscleVolume[$m] = ($muscleVolume[$m] ?? 0) + $v;
                }
            }

            $breakdown = [];
            foreach ($muscleVolume as $muscle => $vol) {
                $breakdown[] = [
                    'name' => $muscle,
                    'value' => $totalPeriodVolume > 0 ? round(($vol / $totalPeriodVolume) * 100, 2) : 0,
                    'raw_volume' => $vol
                ];
            }

            $result[] = [
                'label' => $p['label'],
                'period' => $p['period'],
                'volume' => $totalPeriodVolume,
                'data' => $breakdown
            ];
        }

        return $result;
    }

    public function getProgressiveOverload($user, array $params)
    {
        $exerciseUuid = $params['exercise_uuid'] ?? null;
        $periodType = $params['period_type'] ?? 'month';
        $lookback = $params['lookback'] ?? '4';

        if (!$exerciseUuid) return [];

        $exercise = PhysicalActivitySlot::where('uuid', $exerciseUuid)->first();
        if (!$exercise) return [];

        $data = [];
        $now = Carbon::now();
        $count = $this->getLookbackCount($user, $lookback, $periodType);

        for ($i = $count - 1; $i >= 0; $i--) {
            if ($periodType === 'month') {
                $target = $now->copy()->subMonths($i);
                $start = $target->copy()->startOfMonth();
                $end = $target->copy()->endOfMonth();
                $label = $target->format('M Y');
                $period = $target->format('Y-m');
            } elseif ($periodType === 'week') {
                $target = $now->copy()->subWeeks($i);
                $start = $target->copy()->startOfWeek();
                $end = $target->copy()->endOfWeek();
                $label = 'W' . $target->weekOfYear . ' ' . $target->format('y');
                $period = $start->format('Y-m-d');
            } else { // day
                $start = $now->copy()->subDays($i)->startOfDay();
                $end = $start->copy()->endOfDay();
                $label = $start->format('d M');
                $period = $start->toDateString();
            }
            
            $data[] = [
                'label' => $label,
                'volume' => $this->getVolumeForExerciseInPeriod($user, $exercise->exercise_name, $start, $end),
                'period' => $period
            ];
        }

        return $data;
    }

    private function calculateLogVolume($log)
    {
        $type = $log->slot->metrics_type ?? 'strength';
        $data = $log->metrics_data;
        $volume = 0;

        if ($type === 'endurance' && !isset($data['sets'])) {
            $duration = (float)($data['duration'] ?? 0);
            $weight = (float)($data['weight'] ?? 0);
            return $weight > 0 ? ($duration * $weight) : $duration;
        }

        $sets = $data['sets'] ?? [];
        foreach ($sets as $set) {
            $weight = (float)($set['weight'] ?? 0);
            $reps = (int)($set['reps'] ?? 0);
            $duration = (float)($set['duration'] ?? ($data['duration'] ?? 0));

            $qty = ($type === 'strength') ? $reps : $duration;
            $volume += ($weight > 0) ? ($qty * $weight) : $qty;
        }

        return $volume;
    }

    private function getLookbackCount($user, $lookback, $type)
    {
        if ($lookback === 'all_time') {
            $userJoined = Carbon::parse($user->created_at);
            $now = Carbon::now();

            if ($type === 'month') {
                return $userJoined->diffInMonths($now) + 1;
            } elseif ($type === 'week') {
                return $userJoined->diffInWeeks($now) + 1;
            } else { // day
                return $userJoined->diffInDays($now) + 1;
            }
        }

        if (is_numeric($lookback)) return (int)$lookback;
        if ($lookback === 'this_year') return $type === 'month' ? Carbon::now()->month : Carbon::now()->weekOfYear;
        return 4;
    }

    private function getVolumeForExerciseInPeriod($user, $exerciseName, $start, $end)
    {
        $logs = PhysicalActivityTracker::where('user_uuid', $user->uuid)
            ->whereBetween('activity_date', [$start, $end])
            ->whereHas('slot', function($q) use ($exerciseName) {
                $q->where('exercise_name', $exerciseName);
            })
            ->get();

        $totalVolume = 0;
        foreach ($logs as $log) {
            $totalVolume += $this->calculateLogVolume($log);
        }
        return $totalVolume;
    }
}
