<?php

namespace App\Data\PhysicalActivityData;

use Exception;

class PhysicalActivityFactory
{
  public $type;
  public function __construct(?string $type)
  {
    $this->type = $type;
  }
  public function getPhysicalActivityClass(): PhysicalActivityInterface
  {
    return match ($this->type) {
      'strength_training' => new StrengthTraining(),
      'cardio_training' => new CardioTraining(),
      'flexibility_yoga' => new FlexibilityAndYoga(),
      'balance_core' => new BalanceAndCore(),
      'calisthenics' => new Calisthenics(),
      default => throw new Exception('Invalid physical activity type'),
    };
  }
}