<?php

namespace App\Support;

use App\Models\QuizAttempt;

final class QuizGrader
{
    /**
     * Score every auto-gradable question. If any short answer needs a human,
     * the attempt goes to pending_review and passed stays false until a
     * grader finalises it -- we never pass someone on a partial score.
     */
    public static function grade(QuizAttempt $attempt): QuizAttempt
    {
        $quiz = $attempt->quiz()->with('questions')->first();

        if (! $quiz) {
            return $attempt;
        }

        $answers     = (array) $attempt->answers;
        $totalPoints = 0;
        $score       = 0.0;
        $needsReview = false;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;

            if (! $question->isAutoGradable()) {
                $needsReview = true;
                continue;
            }

            $given = $answers[$question->id] ?? null;

            if (is_array($given)) {
                $given = $given[0] ?? null;
            }

            if ($question->accepts($given)) {
                $score += $question->points;
            }
        }

        $percentage = $totalPoints > 0 ? round($score / $totalPoints * 100, 2) : 0.0;

        $attempt->forceFill([
            'total_points' => $totalPoints,
            'score'        => $score,
            'percentage'   => $percentage,
            'passed'       => ! $needsReview && $percentage >= $quiz->passing_percentage,
            'status'       => $needsReview
                ? QuizAttempt::STATUS_PENDING_REVIEW
                : QuizAttempt::STATUS_GRADED,
            'submitted_at' => $attempt->submitted_at ?? now(),
            'graded_at'    => $needsReview ? null : now(),
        ])->save();

        return $attempt;
    }

    /**
     * Finalise a pending attempt after a grader awards points for the
     * short answers. Auto-graded questions are re-scored from source rather
     * than trusted from the earlier pass.
     */
    public static function applyManualScores(
        QuizAttempt $attempt,
        array $pointsByQuestionId,
        ?int $graderId = null,
    ): QuizAttempt {
        $quiz = $attempt->quiz()->with('questions')->first();

        if (! $quiz) {
            return $attempt;
        }

        $answers     = (array) $attempt->answers;
        $totalPoints = 0;
        $score       = 0.0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;

            if ($question->isAutoGradable()) {
                $given = $answers[$question->id] ?? null;

                if (is_array($given)) {
                    $given = $given[0] ?? null;
                }

                if ($question->accepts($given)) {
                    $score += $question->points;
                }

                continue;
            }

            $awarded = (float) ($pointsByQuestionId[$question->id] ?? 0);
            $score += max(0, min($awarded, $question->points));
        }

        $percentage = $totalPoints > 0 ? round($score / $totalPoints * 100, 2) : 0.0;

        $attempt->forceFill([
            'manual_scores' => $pointsByQuestionId,
            'total_points'  => $totalPoints,
            'score'         => $score,
            'percentage'    => $percentage,
            'passed'        => $percentage >= $quiz->passing_percentage,
            'status'        => QuizAttempt::STATUS_GRADED,
            'graded_at'     => now(),
            'graded_by'     => $graderId,
        ])->save();

        return $attempt;
    }
}
