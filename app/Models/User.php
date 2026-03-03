<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UuidTrait;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function fitnessProfile()
    {
        return $this->hasOne(UserFitnessProfile::class, 'user_uuid', 'uuid');
    }

    public function expenseTracker()
    {
        return $this->hasOne(ExpenseTracker::class, 'user_uuid', 'uuid');
    }
}
