@extends('layouts.public')
@section('title', 'Testing Center')

@section('content')
@include('partials.member-nav')

<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-10">

    @if (session('status'))
        <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <h1 class="font-display text-4xl font-bold uppercase tracking-tight text-ink">Testing Center</h1>
    <p class="mt-2 text-black/55">Rules assessments and certifications.</p>

    <div class="mt-8 space-y-4">
        @foreach ($quizzes as $quiz)
            @php
                $mine      = $attempts[$quiz->id] ?? collect();
                $open      = $mine->firstWhere('status', 'in_progress');
                $best      = $mine->where('status', '!=', 'in_progress')->sortByDesc('percentage')->first();
                $used      = $mine->count();
                $remaining = $quiz->max_attempts ? max(0, $quiz->max_attempts - $used) : null;
                $canStart  = $open || $remaining === null || $remaining > 0;
                $passedIt  = $mine->contains(fn ($a) => $a->passed);
            @endphp

            <div class="rounded-xl border border-black/10 p-6 transition hover:border-gold">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-ink">{{ $quiz->title }}</h2>
                            @if ($passedIt)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Passed</span>
                            @endif
                            @if ($quiz->produces_certificate)
                                <span class="rounded-full bg-gold/20 px-3 py-1 text-xs font-semibold text-ink">Certificate</span>
                            @endif
                        </div>

                        @if ($quiz->course)
                            <p class="mt-1 text-sm text-black/50">Part of {{ $quiz->course->title }}</p>
                        @endif

                        @if ($quiz->description)
                            <p class="mt-2 text-sm text-black/65">{{ $quiz->description }}</p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs uppercase tracking-widest text-black/45">
                            <span>{{ $quiz->questions_count }} questions</span>
                            <span>Pass at {{ $quiz->passing_percentage }}%</span>
                            <span>{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min limit' : 'Untimed' }}</span>
                            <span>{{ $quiz->max_attempts ? $used . ' of ' . $quiz->max_attempts . ' attempts used' : $used . ' attempts, unlimited' }}</span>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        @if ($best)
                            <div class="font-display text-3xl font-bold {{ $best->passed ? 'text-green-600' : 'text-brand-red' }}">
                                {{ rtrim(rtrim((string) $best->percentage, '0'), '.') }}%
                            </div>
                            <a href="{{ route('quizzes.result', $best) }}" class="text-xs font-semibold text-brand hover:text-brand-red">View result</a>
                        @endif

                        <div class="mt-3">
                            @if ($open)
                                <a href="{{ route('quizzes.take', $open) }}"
                                   class="inline-block rounded-md bg-gold px-6 py-3 font-display text-sm font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                                    Resume
                                </a>
                            @elseif ($canStart)
                                <form method="POST" action="{{ route('quizzes.start', $quiz) }}">
                                    @csrf
                                    <button type="submit"
                                            class="rounded-md bg-brand px-6 py-3 font-display text-sm font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
                                        {{ $used > 0 ? 'Retake' : 'Begin' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-xs uppercase tracking-widest text-black/35">No attempts left</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if (count($quizzes) === 0)
            <div class="rounded-xl border-2 border-dashed border-black/15 py-20 text-center text-black/45">
                No assessments are published yet.
            </div>
        @endif
    </div>
</div>
@endsection
