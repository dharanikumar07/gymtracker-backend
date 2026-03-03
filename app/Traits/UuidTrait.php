<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

trait UuidTrait
{
    /**
     * Boot function from Laravel.
     * Logic: Automatically generates a UUID when a model is created.
     */
    protected static function bootUuidTrait()
    {
        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function scopeByUuid(Builder $query, string $uuid): Builder
    {
        return $query->where('uuid', $uuid);
    }

    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->firstOrFail();
    }

    public static function findByUuidOrNull(string $uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    public static function findByUuidIncludeTrash(string $uuid)
    {
        // Only works if the model uses SoftDeletes
        if (method_exists(static::class, 'withTrashed')) {
            return self::withTrashed()->where('uuid', $uuid)->firstOrFail();
        }
        return self::findByUuid($uuid);
    }
}
