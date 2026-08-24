<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Support\QuizGrader;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController
{
    protected function guard(): void
    {
        abort_unless(Auth::user()?->isTrainingMember(), 403,
            'The testing center is available to Officials and Campers.');
    }

    protected function own(QuizAttempt $attempt): void
    {
        abort_unless($attempt->user_id === Auth::id(), 403);
    }

    public function index(): View
    {
        $this->guard();

        $user = Auth::user();

        $quizzes = Quiz::query()
            ->published()
            ->withCount('questions')
            ->with('course')
            ->orderBy('title')
            ->get();

        $attempts = QuizAttempt::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('quiz_id');

        return view('quizzes.index', [
            'quizzes'  => $quizzes,
            'attempts' => $attempts,
            'user'     => $user,
        ]);
    }

    public function start(Quiz $quiz): RedirectResponse
    {
        $this->guard();
        abort_unless($quiz->is_published, 404);

        $user = Auth::user();

        // Resume rather than start a second one.
        $open = QuizAttempt::query()
            ->where('quiz_id', $quiz->getKey())
            ->where('user_id', $user->getKey())
            ->where('status', QuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if ($open) {
            return redirect()->route('quizzes.take', $open);
        }

        if (! $quiz->canBeAttemptedBy($user)) {
            return back()->withErrors(['quiz' => 'You have used all of your attempts for this assessment.']);
        }

        if ($quiz->questions()->count() === 0) {
            return back()->withErrors(['quiz' => 'That assessment has no questions yet.']);
        }

        $attempt = QuizAttempt::create([
            'quiz_id'        => $quiz->getKey(),
            'user_id'        => $user->getKey(),
            'attempt_number' => $quiz->attemptCountFor($user) + 1,
            'status'         => QuizAttempt::STATUS_IN_PROGRESS,
            'answers'        => [],
            'started_at'     => now(),
        ]);

        return redirect()->route('quizzes.take', $attempt);
    }

    public function take(QuizAttempt $attempt): RedirectResponse|View
    {
        $this->guard();
        $this->own($attempt);

        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return redirect()->route('quizzes.result', $attempt);
        }

        // Server-side clock. A paused browser timer must not buy extra time.
        if ($attempt->hasExpired()) {
            QuizGrader::grade($attempt);

            return redirect()->route('quizzes.result', $attempt)
                ->with('status', 'Time expired. Your answers were submitted automatically.');
        }

        $quiz      = $attempt->quiz()->with('questions')->first();
        $questions = $quiz->questions;

        // Stable per-attempt shuffle: the order must not change on refresh.
        if ($quiz->shuffle_questions) {
            $questions = $questions->shuffle($attempt->getKey());
        }

        $deadline = ($quiz->time_limit_minutes && $attempt->started_at)
            ? $attempt->started_at->copy()->addMinutes($quiz->time_limit_minutes)
            : null;

        return view('quizzes.take', [
            'quiz'      => $quiz,
            'attempt'   => $attempt,
            'questions' => $questions,
            'answers'   => (array) $attempt->answers,
            'deadline'  => $deadline,
        ]);
    }

    /** Autosave endpoint. Called on every answer change. */
    public function answer(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->guard();
        $this->own($attempt);

        if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
            return response()->json(['ok' => false, 'reason' => 'closed'], 409);
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'value'       => ['nullable', 'string', 'max:5000'],
        ]);

        $quiz = $attempt->quiz()->with('questions')->first();

        if (! $quiz->questions->contains('id', $data['question_id'])) {
            return response()->json(['ok' => false, 'reason' => 'unknown question'], 422);
        }

        $answers = (array) $attempt->answers;
        $answers[$data['question_id']] = $data['value'];

        $attempt->forceFill(['answers' => $answers])->save();

        $answered = count(array_filter(
            $answers,
            fn ($v) => $v !== null && trim((string) $v) !== '',
        ));

        return response()->json([
            'ok'       => true,
            'answered' => $answered,
            'total'    => $quiz->questions->count(),
            'expired'  => $attempt->hasExpired(),
        ]);
    }

    public function submit(QuizAttempt $attempt): RedirectResponse
    {
        $this->guard();
        $this->own($attempt);

        if ($attempt->status === QuizAttempt::STATUS_IN_PROGRESS) {
            QuizGrader::grade($attempt);
        }

        return redirect()->route('quizzes.result', $attempt);
    }

    public function result(QuizAttempt $attempt): View
    {
        $this->guard();
        $this->own($attempt);

        $quiz = $attempt->quiz()->with('questions')->first();

        return view('quizzes.result', [
            'quiz'      => $quiz,
            'attempt'   => $attempt,
            'questions' => $quiz->questions,
            'answers'   => (array) $attempt->answers,
            'reveal'    => $quiz->reveal_answers,
        ]);
    }
}
