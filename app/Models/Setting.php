<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use UuidTrait;

    protected $fillable = [
        'uuid',
        'user_uuid',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
