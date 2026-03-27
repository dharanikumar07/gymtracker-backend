<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\UuidTrait;

class ExpenseLog extends Model
{
    use HasFactory, UuidTrait;

    protected $guarded = [];

    /**
     * Get the user that owns the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the category associated with the log.
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_uuid', 'uuid');
    }
}
