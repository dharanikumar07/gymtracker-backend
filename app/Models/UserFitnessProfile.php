<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserFitnessProfile extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'data',
        'steps_completed'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
