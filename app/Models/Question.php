<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    public const TYPE_MC    = 'mc';
    public const TYPE_TF    = 'tf';
    public const TYPE_SHORT = 'short';

    public const TYPES = [
        self::TYPE_MC    => 'Multiple choice',
        self::TYPE_TF    => 'True / False',
        self::TYPE_SHORT => 'Short answer',
    ];

    protected $fillable = [
        'quiz_id', 'type', 'prompt', 'options', 'correct_answer',
        'points', 'sort_order', 'explanation',
    ];

    protected $casts = [
        'options'        => 'array',
        'correct_answer' => 'array',
        'points'         => 'integer',
        'sort_order'     => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /** Short answers with no accepted answers listed must be graded by hand. */
    public function isAutoGradable(): bool
    {
        if ($this->type !== self::TYPE_SHORT) {
            return true;
        }

        return filled($this->correct_answer);
    }

    public function accepts(mixed $answer): bool
    {
        $correct = array_map(
            fn ($v) => mb_strtolower(trim((string) $v)),
            (array) $this->correct_answer,
        );

        $given = mb_strtolower(trim((string) $answer));

        return in_array($given, $correct, true);
    }

    protected static function booted(): void
    {
        static::saving(function (self $question) {
            if ($question->type === self::TYPE_TF) {
                $question->options = ['True', 'False'];
            }
        });
    }
}
