<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlan extends Model
{
    use UuidTrait, SoftDeletes;

    protected $table = 'meal_plans';

    protected $guarded = [];

    protected $casts = [
        'food_data' => 'array',
        'calories' => 'float',
        'protein' => 'float',
        'carbs' => 'float',
        'fats' => 'float',
        'nutrition_data' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function logs()
    {
        return $this->hasMany(DietLog::class, 'meal_plan_uuid', 'uuid');
    }
}
