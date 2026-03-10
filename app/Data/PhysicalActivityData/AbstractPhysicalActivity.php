<?php

namespace App\Data\PhysicalActivityData;

use Exception;

abstract class AbstractPhysicalActivity implements PhysicalActivityInterface
{
    abstract public function getData(): array;
    public function getAvailableUnitTypes(): array
    {
        return [
            'weight_units' => ['kg', 'lbs', 'pounds'],
            'duration_units' => ['seconds', 'minutes', 'hours']
        ];
    }
    public function getAvailableMetricTypes(): array
    {
        return [
            'strength',
            'timed_sets',
            'endurance'
        ];
    }
    public function getPhysicalActivityData(?string $type): ?array
    {
        $activity = match ($type) {
            'strength_training' => new StrengthTraining(),
            'cardio' => new CardioTraining(),
            'yoga' => new FlexibilityAndYoga(),
            'balance' => new BalanceAndCore(),
            'calisthenics' => new Calisthenics(),
            default => throw new Exception('Invalid physical activity type'),
        };

        return $activity->getData();
    }
}
