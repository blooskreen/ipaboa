<?php

namespace App\Filament\Pages;

use App\Models\Quiz;
use App\Models\Season;
use App\Models\SectionAccess;
use App\Support\QuizStats;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class QuizStatistics extends Page
{
    protected string $view = 'filament.pages.quiz-statistics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?int $navigationSort = 55;

    public ?int $quizId = null;

    public ?int $seasonId = null;

    public ?string $from = null;

    public ?string $to = null;

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
        return Quiz::query()->orderBy('title')->pluck('title', 'id')->all();
    }

    public function seasonOptions(): array
    {
        return Season::query()->orderByDesc('started_at')->pluck('label', 'id')->all();
    }

    public function quiz(): ?Quiz
    {
        return $this->quizId ? Quiz::with('questions')->find($this->quizId) : null;
    }

    /**
     * A season is just a date window. Picking one overrides the manual
     * from/to boxes rather than fighting with them.
     */
    protected function window(): array
    {
        if ($this->seasonId) {
            $season = Season::find($this->seasonId);

            return [
                $season?->started_at?->toDateString(),
                $season?->ended_at?->toDateString(),
            ];
        }

        return [$this->from ?: null, $this->to ?: null];
    }

    public function attempts(): Collection
    {
        if (! $this->quizId) {
            return collect();
        }

        [$from, $to] = $this->window();

        return QuizStats::attemptsQuery($this->quizId)
            ->when($from, fn ($query) => $query->whereDate('submitted_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('submitted_at', '<=', $to))
            ->with('user')
            ->get();
    }

    public function summary(): array
    {
        return QuizStats::summary($this->attempts());
    }

    public function distribution(): array
    {
        return QuizStats::distribution($this->attempts());
    }

    public function breakdown(): array
    {
        $quiz = $this->quiz();

        return $quiz ? QuizStats::questionBreakdown($quiz, $this->attempts()) : [];
    }

    public function windowLabel(): string
    {
        [$from, $to] = $this->window();

        if (! $from && ! $to) {
            return 'All time';
        }

        return ($from ?: 'the beginning') . ' to ' . ($to ?: 'today');
    }

    public function clearFilters(): void
    {
        $this->seasonId = null;
        $this->from = null;
        $this->to = null;
    }
}
