<?php

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    public const REVEAL_NONE  = 'none';
    public const REVEAL_WRONG = 'wrong';
    public const REVEAL_FULL  = 'full';

    public const REVEAL_OPTIONS = [
        self::REVEAL_NONE  => 'Score only',
        self::REVEAL_WRONG => 'Score and which questions were wrong',
        self::REVEAL_FULL  => 'Score, correct answers and explanations',
    ];

    protected $fillable = [
        'course_id', 'title', 'description', 'instructions', 'passing_percentage',
        'max_attempts', 'time_limit_minutes', 'reveal_answers', 'shuffle_questions',
        'produces_certificate', 'is_published',
    ];

    protected $casts = [
        'passing_percentage'   => 'integer',
        'max_attempts'         => 'integer',
        'time_limit_minutes'   => 'integer',
        'shuffle_questions'    => 'boolean',
        'produces_certificate' => 'boolean',
        'is_published'         => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order')->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function totalPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }

    public function attemptCountFor(User $user): int
    {
        return $this->attempts()->where('user_id', $user->getKey())->count();
    }

    /** Null max_attempts means unlimited. */
    public function attemptsRemainingFor(User $user): ?int
    {
        if ($this->max_attempts === null) {
            return null;
        }

        return max(0, $this->max_attempts - $this->attemptCountFor($user));
    }

    public function canBeAttemptedBy(User $user): bool
    {
        if (! $this->is_published) {
            return false;
        }

        $remaining = $this->attemptsRemainingFor($user);

        return $remaining === null || $remaining > 0;
    }

    public function bestAttemptFor(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->getKey())
            ->orderByDesc('percentage')
            ->first();
    }

    public static function graderRoles(): array
    {
        return [Roles::SUPER, Roles::ADMIN, Roles::CAMP_ADMIN, Roles::LEADERSHIP];
    }
}
