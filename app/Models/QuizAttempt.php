<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    public const STATUS_IN_PROGRESS   = 'in_progress';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_GRADED        = 'graded';

    public const STATUSES = [
        self::STATUS_IN_PROGRESS    => 'In progress',
        self::STATUS_PENDING_REVIEW => 'Pending review',
        self::STATUS_GRADED         => 'Graded',
    ];

    protected $fillable = [
        'quiz_id', 'user_id', 'attempt_number', 'status', 'score', 'total_points',
        'percentage', 'passed', 'answers', 'manual_scores', 'started_at', 'submitted_at',
        'graded_at', 'graded_by',
    ];

    protected $casts = [
        'answers'      => 'array',
        'manual_scores' => 'array',
        'score'        => 'decimal:2',
        'percentage'   => 'decimal:2',
        'total_points' => 'integer',
        'passed'       => 'boolean',
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    protected static function booted(): void
    {
        static::saved(function (self $attempt) {
            \App\Support\Certificates::issueForQuizAttempt($attempt);
        });
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    /** Server-side clock. A browser timer alone is trivially bypassed. */
    public function hasExpired(): bool
    {
        $limit = $this->quiz?->time_limit_minutes;

        if (! $limit || ! $this->started_at) {
            return false;
        }

        return now()->greaterThan($this->started_at->copy()->addMinutes($limit));
    }
}
