<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhysicalActivitySlot extends Model
{
    use UuidTrait, SoftDeletes;

    protected $table = 'physical_activity_slots';

    protected $guarded = [];

    protected $casts = [
        'metrics_data' => 'array',
        'meta_data' => 'array',
        ' exercise_order' => 'integer'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'physical_activity_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
