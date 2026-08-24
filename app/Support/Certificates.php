<?php

namespace App\Support;

use App\Models\Certificate;
use App\Models\CourseCompletion;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Certificates
{
    public static function issueForCourseCompletion(CourseCompletion $completion): ?Certificate
    {
        if ($completion->status !== CourseCompletion::STATUS_APPROVED) {
            return null;
        }

        $course = $completion->course;

        if (! $course || ! $course->produces_certificate) {
            return null;
        }

        return self::award($completion->user_id, $completion, $course->title, [
            'hours'  => (string) $completion->hours_credited,
            'season' => $completion->season,
            'kind'   => 'course',
        ]);
    }

    public static function issueForQuizAttempt(QuizAttempt $attempt): ?Certificate
    {
        if (! $attempt->passed) {
            return null;
        }

        $quiz = $attempt->quiz;

        if (! $quiz || ! $quiz->produces_certificate) {
            return null;
        }

        return self::award($attempt->user_id, $attempt, $quiz->title, [
            'percentage' => (string) $attempt->percentage,
            'score'      => (string) $attempt->score,
            'total'      => (string) $attempt->total_points,
            'kind'       => 'assessment',
        ]);
    }

    /**
     * Idempotent by (certifiable_type, certifiable_id) -- re-saving an
     * approved completion or re-grading an attempt cannot mint a duplicate.
     */
    protected static function award(int $userId, Model $source, string $title, array $meta = []): Certificate
    {
        return Certificate::firstOrCreate(
            [
                'certifiable_type' => $source->getMorphClass(),
                'certifiable_id'   => $source->getKey(),
            ],
            [
                'user_id'   => $userId,
                'title'     => $title,
                'serial'    => self::serial(),
                'issued_at' => now(),
                'meta'      => $meta,
            ],
        );
    }

    protected static function serial(): string
    {
        do {
            $serial = 'IPABOA-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
        } while (Certificate::where('serial', $serial)->exists());

        return $serial;
    }
}
