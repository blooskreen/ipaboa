<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Certificate extends Model
{
    protected $fillable = [
        'user_id', 'certifiable_type', 'certifiable_id',
        'title', 'serial', 'issued_at', 'meta',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'meta'      => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /** "Course" or "Assessment", for display. */
    public function sourceLabel(): string
    {
        return match ($this->certifiable_type) {
            CourseCompletion::class => 'Course',
            QuizAttempt::class      => 'Assessment',
            default                 => 'Award',
        };
    }
}
