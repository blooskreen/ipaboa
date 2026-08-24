<?php

namespace App\Support;

use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\Season;
use App\Models\User;

final class Enrollment
{
    public static function for(User $user, Course $course): ?CourseCompletion
    {
        $season = Season::currentLabel();

        return CourseCompletion::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $course->getKey())
            ->where('season', $season)
            ->first();
    }

    public static function enroll(User $user, Course $course): CourseCompletion
    {
        $existing = self::for($user, $course);

        if ($existing) {
            return $existing;
        }

        return CourseCompletion::create([
            'user_id'        => $user->getKey(),
            'course_id'      => $course->getKey(),
            'status'         => CourseCompletion::STATUS_ENROLLED,
            'hours_credited' => 0,
            'season'         => Season::currentLabel(),
        ]);
    }

    /**
     * Mark a course finished.
     *
     * The course's requires_approval flag is the whole decision: off means
     * credit the hours now, on means queue it for leadership. Certificates
     * ride along automatically via CourseCompletion's saved hook.
     */
    public static function complete(User $user, Course $course): CourseCompletion
    {
        $completion = self::enroll($user, $course);

        if ($completion->status === CourseCompletion::STATUS_APPROVED) {
            return $completion;
        }

        if ($course->requires_approval) {
            $completion->forceFill([
                'status'       => CourseCompletion::STATUS_PENDING,
                'completed_at' => $completion->completed_at ?? now(),
            ])->save();

            return $completion;
        }

        $completion->forceFill([
            'status'         => CourseCompletion::STATUS_APPROVED,
            'hours_credited' => (float) $course->hours,
            'completed_at'   => $completion->completed_at ?? now(),
            'approved_at'    => now(),
            'approved_by'    => null,   // auto-approved by the course setting
        ])->save();

        return $completion;
    }
}
