<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DietLog extends Model
{
    use UuidTrait, SoftDeletes;

    protected $table = 'diet_logs';

    protected $guarded = [];

    protected $casts = [
        'actual_quantity' => 'decimal:2',
        'calories' => 'float',
        'protein' => 'float',
        'carbs' => 'float',
        'fats' => 'float',
        'food_data' => 'array',
        'nutrition_data' => 'array',
    ];

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_uuid', 'uuid');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
