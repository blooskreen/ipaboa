<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCompletion extends Model
{
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';

    public const STATUSES = [
        self::STATUS_ENROLLED => 'Enrolled',
        self::STATUS_PENDING  => 'Pending approval',
        self::STATUS_APPROVED => 'Approved',
    ];

    protected $fillable = [
        'user_id', 'course_id', 'status', 'hours_credited', 'season',
        'completed_at', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'hours_credited' => 'decimal:2',
        'completed_at'   => 'datetime',
        'approved_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    protected static function booted(): void
    {
        // Stamp the season at enrollment. The unique key is
        // (user_id, course_id, season), so a null season would let a member
        // silently double-enroll.
        static::saved(function (self $completion) {
            \App\Support\Certificates::issueForCourseCompletion($completion);
        });

        static::creating(function (self $completion) {
            if (blank($completion->season)) {
                $completion->season = Season::currentLabel();
            }
        });
    }
}
