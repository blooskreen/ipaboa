<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseCompletion;
use App\Support\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController
{
    /** Courses are for people in the training program, not every account. */
    protected function guard(): void
    {
        abort_unless(Auth::user()?->isTrainingMember(), 403,
            'Course enrollment is available to Officials and Campers. Contact leadership to be added.');
    }

    public function index(Request $request): View
    {
        $this->guard();

        $user       = Auth::user();
        $categoryId = $request->integer('category') ?: null;

        $courses = Course::query()
            ->published()
            ->with('categories')
            ->when($categoryId, fn ($query) => $query->whereHas(
                'categories',
                fn ($q) => $q->where('course_categories.id', $categoryId),
            ))
            ->orderBy('title')
            ->get();

        $mine = CourseCompletion::query()
            ->where('user_id', $user->getKey())
            ->pluck('status', 'course_id');

        return view('courses.index', [
            'courses'    => $courses,
            'categories' => CourseCategory::query()->orderBy('sort_order')->get(),
            'categoryId' => $categoryId,
            'mine'       => $mine,
        ]);
    }

    public function show(Course $course): View
    {
        $this->guard();
        abort_unless($course->is_published, 404);

        $course->load('categories');

        return view('courses.show', [
            'course'     => $course,
            'completion' => Enrollment::for(Auth::user(), $course),
        ]);
    }

    public function enroll(Course $course): RedirectResponse
    {
        $this->guard();
        abort_unless($course->is_published, 404);

        Enrollment::enroll(Auth::user(), $course);

        return back()->with('status', 'You are enrolled. Work through the material, then mark it complete.');
    }

    public function complete(Course $course): RedirectResponse
    {
        $this->guard();
        abort_unless($course->is_published, 404);

        $completion = Enrollment::complete(Auth::user(), $course);

        $message = $completion->status === CourseCompletion::STATUS_APPROVED
            ? 'Complete. ' . rtrim(rtrim((string) $completion->hours_credited, '0'), '.') . ' hours credited.'
            : 'Submitted. Leadership will review it and credit your hours.';

        return back()->with('status', $message);
    }
}
