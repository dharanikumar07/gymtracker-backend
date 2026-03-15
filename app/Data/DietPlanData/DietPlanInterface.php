<?php

namespace App\Data\DietPlanData;

interface DietPlanInterface
{
    public function generate(float $weight): array;
}
