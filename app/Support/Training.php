<?php

namespace App\Support;

use App\Models\CourseCompletion;
use App\Models\Season;
use App\Models\User;

final class Training
{
    /** Moves to the settings table in Phase 7; a constant is fine until then. */
    public const HOURS_REQUIRED = 8.0;

    public const REQUIREMENT_NAME = 'IPABOA Camp Requirements';

    public static function hoursRequired(): float
    {
        return self::HOURS_REQUIRED;
    }

    /** Approved hours only. Enrolled and pending completions do not count. */
    public static function cifHours(User $user, ?string $seasonLabel = null): float
    {
        $season = $seasonLabel ?? Season::currentLabel();

        if (blank($season)) {
            return 0.0;
        }

        return (float) CourseCompletion::query()
            ->where('user_id', $user->getKey())
            ->where('season', $season)
            ->approved()
            ->sum('hours_credited');
    }

    public static function percent(User $user, ?string $seasonLabel = null): float
    {
        $required = self::hoursRequired();

        if ($required <= 0) {
            return 100.0;
        }

        return min(100.0, round(self::cifHours($user, $seasonLabel) / $required * 100, 1));
    }

    public static function isMet(User $user, ?string $seasonLabel = null): bool
    {
        return self::cifHours($user, $seasonLabel) >= self::hoursRequired();
    }

    /** Past Seasons history for the member dashboard. */
    public static function history(User $user): array
    {
        return CourseCompletion::query()
            ->where('user_id', $user->getKey())
            ->approved()
            ->whereNotNull('season')
            ->selectRaw('season, sum(hours_credited) as hours')
            ->groupBy('season')
            ->orderByDesc('season')
            ->get()
            ->map(fn ($row) => [
                'season'   => $row->season,
                'hours'    => (float) $row->hours,
                'required' => self::hoursRequired(),
                'met'      => (float) $row->hours >= self::hoursRequired(),
            ])
            ->all();
    }
}
