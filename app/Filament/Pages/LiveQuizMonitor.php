<?php

namespace App\Filament\Pages;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SectionAccess;
use App\Support\QuizStats;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LiveQuizMonitor extends Page
{
    protected string $view = 'filament.pages.live-quiz-monitor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?int $navigationSort = 45;

    /** Bound to the quiz picker. */
    public ?int $quizId = null;

    /** live | ranking | review */
    public string $mode = 'live';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Education';
    }

    public static function canAccess(): bool
    {
        return SectionAccess::allows('Education');
    }

    public function quizOptions(): array
    {
        return Quiz::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    public function quiz(): ?Quiz
    {
        return $this->quizId
            ? Quiz::with('questions')->find($this->quizId)
            : null;
    }

    /**
     * Every value the view needs is precomputed here, including colours and
     * bar widths. Livewire's runtime Blade compiler rejects @if/@else inside
     * a @foreach, so the template must be able to render without branching.
     */
    public function rows(): array
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return [];
        }

        $totalQuestions = max(1, $quiz->questions->count());

        $attempts = QuizAttempt::query()
            ->where('quiz_id', $quiz->getKey())
            ->where(function ($query) {
                $query->where('status', QuizAttempt::STATUS_IN_PROGRESS)
                    ->orWhere('updated_at', '>=', now()->subHours(12));
            })
            ->with('user')
            ->get();

        $rows = [];

        foreach ($attempts as $attempt) {
            $answers = (array) $attempt->answers;

            $answered = 0;
            foreach ($answers as $value) {
                if (is_array($value)) {
                    $value = $value[0] ?? null;
                }

                if ($value !== null && trim((string) $value) !== '') {
                    $answered++;
                }
            }

            $answered = min($answered, $totalQuestions);
            $progress = (int) round($answered / $totalQuestions * 100);

            $isDone = $attempt->status !== QuizAttempt::STATUS_IN_PROGRESS;

            if (! $isDone) {
                $color  = '#f59e0b';
                $label  = 'In progress';
                $score  = $answered . ' of ' . $totalQuestions . ' answered';
            } elseif ($attempt->status === QuizAttempt::STATUS_PENDING_REVIEW) {
                $color    = '#3b82f6';
                $label    = 'Awaiting grading';
                $progress = 100;
                $score    = 'Submitted';
            } elseif ($attempt->passed) {
                $color    = '#16a34a';
                $label    = 'Passed';
                $progress = 100;
                $score    = $attempt->percentage . '%  (' . rtrim(rtrim((string) $attempt->score, '0'), '.') . '/' . $attempt->total_points . ')';
            } else {
                $color    = '#dc2626';
                $label    = 'Did not pass';
                $progress = 100;
                $score    = $attempt->percentage . '%  (' . rtrim(rtrim((string) $attempt->score, '0'), '.') . '/' . $attempt->total_points . ')';
            }

            $elapsed = $attempt->started_at
                ? $attempt->started_at->diffInMinutes(now()) . ' min'
                : '--';

            $rows[] = [
                'key'      => $attempt->getKey(),
                'name'     => $attempt->user?->name ?? 'Unknown',
                'email'    => $attempt->user?->email ?? '',
                'progress' => $progress,
                'color'    => $color,
                'label'    => $label,
                'score'    => $score,
                'elapsed'  => $elapsed,
                'sort'     => ($isDone ? 1 : 0) . strtolower($attempt->user?->name ?? ''),
            ];
        }

        usort($rows, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $rows;
    }

    public function finishedAttempts()
    {
        if (! $this->quizId) {
            return collect();
        }

        return QuizStats::attemptsQuery($this->quizId)->with('user')->get();
    }

    public function ranking(): array
    {
        return QuizStats::ranking($this->finishedAttempts());
    }

    public function summary(): array
    {
        return QuizStats::summary($this->finishedAttempts());
    }

    public function review(): array
    {
        $quiz = $this->quiz();

        return $quiz ? QuizStats::questionBreakdown($quiz, $this->finishedAttempts()) : [];
    }

    public function counts(): array
    {
        $rows = $this->rows();

        return [
            'total'      => count($rows),
            'inProgress' => count(array_filter($rows, fn ($r) => $r['label'] === 'In progress')),
            'finished'   => count(array_filter($rows, fn ($r) => $r['label'] !== 'In progress')),
        ];
    }
}
