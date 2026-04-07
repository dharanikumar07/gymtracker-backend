<?php

namespace App\Data\PhysicalActivityData;

interface PhysicalActivityInterface
{
    public function getData(): array;
    public function getAvailableUnitTypes(): array;
    public function getAvailableMetricTypes(): array;
}
