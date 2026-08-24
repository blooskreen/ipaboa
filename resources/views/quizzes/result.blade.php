@extends('layouts.public')
@section('title', 'Result')

@section('content')
@include('partials.member-nav')

@php
    $pending = $attempt->status === 'pending_review';
    if ($pending)            { $tone = '#3b82f6'; $word = 'Awaiting grading'; }
    elseif ($attempt->passed){ $tone = '#16a34a'; $word = 'Passed'; }
    else                     { $tone = '#C8102E'; $word = 'Did not pass'; }
@endphp

<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">

    @if (session('status'))
        <div class="mb-6 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('status') }}</div>
    @endif

    <a href="{{ route('quizzes.index') }}" class="text-sm font-semibold text-brand hover:text-brand-red">&larr; Testing center</a>

    <div class="mt-6 rounded-xl border-2 p-8 text-center" style="border-color: {{ $tone }}">
        <div class="font-display text-xs font-bold uppercase tracking-[0.3em]" style="color: {{ $tone }}">{{ $word }}</div>
        <div class="mt-2 font-display text-7xl font-bold text-ink">{{ rtrim(rtrim((string) $attempt->percentage, '0'), '.') }}%</div>
        <div class="mt-1 text-black/55">
            {{ rtrim(rtrim((string) $attempt->score, '0'), '.') }} of {{ $attempt->total_points }} points
            &middot; pass mark {{ $quiz->passing_percentage }}%
        </div>
        <div class="mt-1 text-xs uppercase tracking-widest text-black/35">
            {{ $quiz->title }} &middot; attempt {{ $attempt->attempt_number }}
        </div>

        @if ($pending)
            <p class="mx-auto mt-5 max-w-md text-sm text-black/60">
                This assessment has written answers that a grader reviews by hand. Your score
                may change once that is done, and you will see it here.
            </p>
        @endif
    </div>

    @if ($reveal !== 'none')
        <h2 class="mt-10 font-display text-2xl font-bold uppercase tracking-wide text-ink">Your answers</h2>

        @foreach ($questions as $i => $q)
            @php
                $given = $answers[$q->id] ?? ($answers[(string) $q->id] ?? null);
                $given = is_array($given) ? ($given[0] ?? null) : $given;
                $given = trim((string) $given);

                if (! $q->isAutoGradable()) {
                    $mark = 'Hand graded'; $markTone = 'text-blue-700'; $border = 'border-blue-200';
                } elseif ($given !== '' && $q->accepts($given)) {
                    $mark = 'Correct';     $markTone = 'text-green-700'; $border = 'border-green-200';
                } else {
                    $mark = 'Incorrect';   $markTone = 'text-red-700';   $border = 'border-red-200';
                }
            @endphp

            <div class="mt-4 rounded-xl border {{ $border }} p-5">
                <div class="flex items-start justify-between gap-4">
                    <p class="font-semibold text-ink">{{ $i + 1 }}. {{ $q->prompt }}</p>
                    <span class="shrink-0 font-display text-xs font-bold uppercase tracking-widest {{ $markTone }}">{{ $mark }}</span>
                </div>

                <p class="mt-3 text-sm text-black/70">
                    <span class="font-semibold">Your answer:</span>
                    {{ $given !== '' ? $given : 'left blank' }}
                </p>

                @if ($reveal === 'full' && filled($q->correct_answer))
                    <p class="mt-1 text-sm text-green-800">
                        <span class="font-semibold">Correct answer:</span> {{ implode(' / ', (array) $q->correct_answer) }}
                    </p>
                @endif

                @if ($reveal === 'full' && $q->explanation)
                    <p class="mt-2 rounded-md bg-black/[0.03] px-3 py-2 text-sm text-black/65">{{ $q->explanation }}</p>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection
