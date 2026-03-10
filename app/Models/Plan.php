<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use UuidTrait, SoftDeletes;

    public const PHYSICAL_ACTIVITY_TYPE = "physical_activity";

    protected $table = 'plans';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function physicalActivitySlots()
    {
        return $this->hasMany(PhysicalActivitySlot::class, 'plan_uuid', 'uuid');
    }
}
