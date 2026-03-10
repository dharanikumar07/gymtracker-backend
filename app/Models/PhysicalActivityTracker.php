<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalActivityTracker extends Model
{
    protected $table = 'physical_activity_tracker';

    protected $guarded = [];

    protected $casts = [
        'metrics_data' => 'array',
        'activity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function slot()
    {
        return $this->belongsTo(PhysicalActivitySlot::class, 'slot_uuid', 'uuid');
    }
}
