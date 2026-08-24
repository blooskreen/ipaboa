<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['label', 'is_current', 'started_at', 'ended_at'];

    protected $casts = [
        'is_current' => 'boolean',
        'started_at' => 'date',
        'ended_at'   => 'date',
    ];

    protected static ?Season $currentCache = null;

    public static function current(): ?self
    {
        if (static::$currentCache === null) {
            static::$currentCache = static::query()->where('is_current', true)->first();
        }

        return static::$currentCache;
    }

    public static function currentLabel(): ?string
    {
        return static::current()?->label;
    }

    public static function flush(): void
    {
        static::$currentCache = null;
    }

    protected static function booted(): void
    {
        // Exactly one current season. Uses a query-builder update so this
        // does not recurse back through model events.
        static::saved(function (self $season) {
            if ($season->is_current) {
                static::query()
                    ->whereKeyNot($season->getKey())
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            static::flush();
        });

        static::deleted(fn () => static::flush());
    }
}
