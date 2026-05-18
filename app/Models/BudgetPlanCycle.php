<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPlanCycle extends Model
{
    use HasFactory, UuidTrait;

    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'json',
        'cycle_start' => 'date',
        'cycle_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_uuid', 'uuid');
    }

    public function logs()
    {
        return $this->hasMany(ExpenseLog::class, 'plan_cycle_uuid', 'uuid');
    }
}
