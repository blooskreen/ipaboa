@extends('layouts.public')
@section('title', $course->title)

@section('content')
@include('partials.member-nav')

@php
    $embed  = $course->embedUrl();
    $img    = $course->imageUrl();
    $status = $completion?->status;

    $banner = match ($status) {
        'approved' => ['Completed', 'You have been credited ' . rtrim(rtrim((string) $completion->hours_credited, '0'), '.') . ' hours for this course.', 'border-green-300 bg-green-50 text-green-900'],
        'pending'  => ['Awaiting approval', 'Leadership will review this and credit your hours.', 'border-amber-300 bg-amber-50 text-amber-900'],
        'enrolled' => ['Enrolled', 'Work through the material below, then mark it complete.', 'border-brand/30 bg-brand/5 text-brand'],
        default    => null,
    };
@endphp

<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-10">

    @if (session('status'))
        <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('courses.index') }}" class="text-sm font-semibold text-brand hover:text-brand-red">&larr; All courses</a>

    <div class="mt-4 flex flex-wrap gap-1.5">
        @foreach ($course->categories as $cat)
            <span class="rounded-sm bg-black/[0.06] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-black/55">{{ $cat->name }}</span>
        @endforeach
    </div>

    <h1 class="mt-3 font-display text-4xl sm:text-5xl font-bold uppercase leading-[0.95] tracking-tight text-ink">
        {{ $course->title }}
    </h1>

    <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-black/60">
        <span class="font-display font-bold uppercase tracking-wider text-gold">
            {{ rtrim(rtrim((string) $course->hours, '0'), '.') }} training hours
        </span>
        @if ($course->instructorList() !== '')
            <span class="flex items-center gap-1.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0v.75h-15v-.75z"/>
                </svg>
                {{ $course->instructorList() }}
            </span>
        @endif
        <span>{{ $course->requires_approval ? 'Hours require approval' : 'Hours credited instantly' }}</span>
    </div>

    @if ($banner)
        <div class="mt-6 rounded-lg border px-4 py-3 text-sm {{ $banner[2] }}">
            <span class="font-semibold">{{ $banner[0] }}.</span> {{ $banner[1] }}
        </div>
    @endif

    @if ($embed)
        <div class="mt-8 aspect-video w-full overflow-hidden rounded-xl bg-black">
            <iframe src="{{ $embed }}" class="h-full w-full" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture"
                    allowfullscreen title="{{ $course->title }}"></iframe>
        </div>
    @elseif ($img)
        <img src="{{ $img }}" alt="" class="mt-8 w-full rounded-xl object-cover">
    @endif

    @if ($course->description)
        <div class="prose-ipaboa mt-8 text-[15px] leading-relaxed text-black/75">
            {!! $course->description !!}
        </div>
    @endif

    @if ($course->body)
        <div class="prose-ipaboa mt-8 border-t border-black/10 pt-8 text-[15px] leading-relaxed text-black/75">
            {!! $course->body !!}
        </div>
    @endif

    @if ($course->content_url && ! $embed)
        <a href="{{ $course->content_url }}" target="_blank" rel="noopener"
           class="mt-8 inline-block rounded-md border-2 border-brand px-6 py-3 font-display text-sm font-bold uppercase tracking-wider text-brand hover:bg-brand hover:text-white transition">
            Open course material
        </a>
    @endif

    <div class="mt-10 flex flex-wrap gap-3 border-t border-black/10 pt-8">
        @if (! $completion)
            <form method="POST" action="{{ route('courses.enroll', $course) }}">
                @csrf
                <button type="submit"
                        class="rounded-md bg-brand px-8 py-3.5 font-display text-sm font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
                    Enroll in this course
                </button>
            </form>
        @endif

        @if ($status === 'enrolled')
            <form method="POST" action="{{ route('courses.complete', $course) }}"
                  onsubmit="return confirm('Mark this course complete?');">
                @csrf
                <button type="submit"
                        class="rounded-md bg-gold px-8 py-3.5 font-display text-sm font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                    Mark complete
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
