<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationLog extends Model
{
    use UuidTrait, SoftDeletes;

    protected $table = 'notification_logs';

    protected $guarded = [];

    protected $casts = [
        'notification_date' => 'date',
        'actual_notification_time_sended' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
