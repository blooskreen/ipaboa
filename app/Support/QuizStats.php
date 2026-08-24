<?php

namespace App\Support;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Collection;

final class QuizStats
{
    /** Only finished attempts count toward stats. In-progress ones are noise. */
    public static function attemptsQuery(int $quizId)
    {
        return QuizAttempt::query()
            ->where('quiz_id', $quizId)
            ->whereIn('status', [
                QuizAttempt::STATUS_PENDING_REVIEW,
                QuizAttempt::STATUS_GRADED,
            ]);
    }

    /**
     * json_decode gives string keys, PHP coerces numeric ones to int -- but
     * not always across serialisation boundaries, so check both.
     */
    public static function answerFor(array $answers, int $questionId): ?string
    {
        $value = $answers[$questionId] ?? $answers[(string) $questionId] ?? null;

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public static function ranking(Collection $attempts): array
    {
        $sorted = $attempts
            ->sortByDesc(fn (QuizAttempt $a) => (float) $a->percentage)
            ->values();

        $rows = [];
        $rank = 0;
        $lastPct = null;
        $index = 0;

        foreach ($sorted as $attempt) {
            $index++;
            $pct = (float) $attempt->percentage;

            if ($lastPct === null || $pct < $lastPct) {
                $rank = $index;
                $lastPct = $pct;
            }

            if ($attempt->status === QuizAttempt::STATUS_PENDING_REVIEW) {
                $color = '#3b82f6';
                $label = 'Awaiting grading';
            } elseif ($attempt->passed) {
                $color = '#16a34a';
                $label = 'Passed';
            } else {
                $color = '#dc2626';
                $label = 'Did not pass';
            }

            $medal = match ($rank) {
                1       => '#d4af37',
                2       => '#9ca3af',
                3       => '#b45309',
                default => 'transparent',
            };

            $minutes = ($attempt->started_at && $attempt->submitted_at)
                ? $attempt->started_at->diffInMinutes($attempt->submitted_at) . ' min'
                : '--';

            $rows[] = [
                'rank'    => $rank,
                'medal'   => $medal,
                'name'    => $attempt->user?->name ?? 'Unknown',
                'email'   => $attempt->user?->email ?? '',
                'pct'     => number_format($pct, 1),
                'width'   => max(0, min(100, (int) round($pct))),
                'score'   => rtrim(rtrim((string) $attempt->score, '0'), '.') . ' / ' . $attempt->total_points,
                'color'   => $color,
                'label'   => $label,
                'minutes' => $minutes,
            ];
        }

        return $rows;
    }

    public static function summary(Collection $attempts): array
    {
        $count = $attempts->count();

        if ($count === 0) {
            return [
                'count' => 0, 'average' => '0.0', 'median' => '0.0',
                'passRate' => '0.0', 'high' => '0.0', 'low' => '0.0', 'avgMinutes' => '--',
            ];
        }

        $pcts = $attempts->map(fn (QuizAttempt $a) => (float) $a->percentage)->sort()->values();
        $mid  = intdiv($count, 2);

        $median = $count % 2 === 0
            ? (($pcts[$mid - 1] + $pcts[$mid]) / 2)
            : $pcts[$mid];

        $durations = $attempts
            ->filter(fn (QuizAttempt $a) => $a->started_at && $a->submitted_at)
            ->map(fn (QuizAttempt $a) => $a->started_at->diffInMinutes($a->submitted_at));

        return [
            'count'      => $count,
            'average'    => number_format($pcts->avg(), 1),
            'median'     => number_format($median, 1),
            'passRate'   => number_format($attempts->where('passed', true)->count() / $count * 100, 1),
            'high'       => number_format($pcts->last(), 1),
            'low'        => number_format($pcts->first(), 1),
            'avgMinutes' => $durations->count() ? round($durations->avg()) . ' min' : '--',
        ];
    }

    /** Per-question correctness, hardest first, with distractor counts for MC. */
    public static function questionBreakdown(Quiz $quiz, Collection $attempts): array
    {
        $quiz->loadMissing('questions');
        $total = max(1, $attempts->count());
        $out   = [];

        foreach ($quiz->questions as $question) {
            $correct  = 0;
            $blank    = 0;
            $tally    = [];

            foreach ($attempts as $attempt) {
                $given = self::answerFor((array) $attempt->answers, $question->id);

                if ($given === null) {
                    $blank++;
                } elseif (in_array($question->type, [Question::TYPE_MC, Question::TYPE_TF], true)) {
                    $tally[$given] = ($tally[$given] ?? 0) + 1;
                }

                if ($question->isAutoGradable()) {
                    if ($given !== null && $question->accepts($given)) {
                        $correct++;
                    }
                } else {
                    $manual  = (array) $attempt->manual_scores;
                    $awarded = (float) ($manual[$question->id] ?? $manual[(string) $question->id] ?? 0);

                    if ($awarded >= $question->points) {
                        $correct++;
                    }
                }
            }

            $rate = round($correct / $total * 100, 1);

            if ($rate >= 80) {
                $rateColor = '#16a34a';
            } elseif ($rate >= 50) {
                $rateColor = '#f59e0b';
            } else {
                $rateColor = '#dc2626';
            }

            $answerKey = (array) $question->correct_answer;
            $choices   = [];

            foreach ((array) $question->options as $option) {
                $picked    = $tally[$option] ?? 0;
                $isCorrect = in_array($option, $answerKey, true);

                $choices[] = [
                    'text'    => $option,
                    'count'   => $picked,
                    'pct'     => number_format($picked / $total * 100, 1),
                    'width'   => max(0, min(100, (int) round($picked / $total * 100))),
                    'color'   => $isCorrect ? '#16a34a' : '#dc2626',
                    'marker'  => $isCorrect ? 'Correct' : 'Incorrect',
                ];
            }

            $out[] = [
                'id'        => $question->id,
                'prompt'    => $question->prompt,
                'typeLabel' => Question::TYPES[$question->type] ?? $question->type,
                'points'    => $question->points,
                'correct'   => $correct,
                'total'     => $attempts->count(),
                'blank'     => $blank,
                'rate'      => number_format($rate, 1),
                'width'     => max(0, min(100, (int) round($rate))),
                'rateColor' => $rateColor,
                'answerKey' => $answerKey !== [] ? implode(' / ', $answerKey) : 'Hand graded',
                'choices'   => $choices,
            ];
        }

        usort($out, fn ($a, $b) => (float) $a['rate'] <=> (float) $b['rate']);

        return $out;
    }

    /** Ten buckets of 10% for a score histogram. */
    public static function distribution(Collection $attempts): array
    {
        $buckets = array_fill(0, 10, 0);

        foreach ($attempts as $attempt) {
            $pct = max(0.0, min(100.0, (float) $attempt->percentage));
            $buckets[min(9, (int) floor($pct / 10))]++;
        }

        $max  = max(1, max($buckets));
        $out  = [];

        foreach ($buckets as $i => $count) {
            $out[] = [
                'label'  => ($i * 10) . '-' . ($i * 10 + 9) . '%',
                'count'  => $count,
                'height' => (int) round($count / $max * 100),
            ];
        }

        return $out;
    }
}
