<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DietPlanItem extends Model
{
    use UuidTrait, SoftDeletes;

    protected $table = 'diet_plan_items';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'calories' => 'integer',
        'protein' => 'integer',
        'carbs' => 'integer',
        'fats' => 'integer',
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
        return $this->hasMany(DietLog::class, 'diet_plan_item_uuid', 'uuid');
    }
}
