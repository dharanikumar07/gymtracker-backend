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
            'endurance',
            'rest'
        ];
    }
}
