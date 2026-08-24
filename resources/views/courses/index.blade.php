@extends('layouts.public')
@section('title', 'Courses')

@section('content')
@include('partials.member-nav')

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

    @if (session('status'))
        <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-8">
        <h1 class="font-display text-4xl font-bold uppercase tracking-tight text-ink">Courses</h1>
        <p class="mt-2 text-black/55">
            Enroll, work through the material, then mark it complete. Approved hours count toward
            your {{ (int) \App\Support\Training::hoursRequired() }}-hour season requirement.
        </p>
    </div>

    <div class="mb-8 flex flex-wrap gap-2">
        <a href="{{ route('courses.index') }}"
           class="rounded-full border px-4 py-1.5 font-display text-xs font-bold uppercase tracking-wider transition
                  {{ $categoryId ? 'border-black/20 text-black/55 hover:border-brand hover:text-brand' : 'border-brand bg-brand text-white' }}">
            All
        </a>
        @foreach ($categories as $cat)
            @php $on = $categoryId === $cat->id; @endphp
            <a href="{{ route('courses.index', ['category' => $cat->id]) }}"
               class="rounded-full border px-4 py-1.5 font-display text-xs font-bold uppercase tracking-wider transition
                      {{ $on ? 'border-brand bg-brand text-white' : 'border-black/20 text-black/55 hover:border-brand hover:text-brand' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($courses as $course)
            @php
                $status = $mine[$course->id] ?? null;
                $pill = match ($status) {
                    'approved' => ['Completed',  'bg-green-100 text-green-800'],
                    'pending'  => ['Awaiting approval', 'bg-amber-100 text-amber-800'],
                    'enrolled' => ['Enrolled',   'bg-brand/10 text-brand'],
                    default    => null,
                };
                $img = $course->imageUrl();
                $instructors = $course->instructorList();
            @endphp

            <a href="{{ route('courses.show', $course) }}"
               class="group flex flex-col overflow-hidden rounded-xl border border-black/10 transition hover:border-gold hover:shadow-lg">

                <div class="relative aspect-video w-full bg-gradient-to-br from-brand-dark to-brand">
                    @if ($img)
                        <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
                    @endif
                    @if ($pill)
                        <span class="absolute right-2 top-2 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $pill[1] }}">{{ $pill[0] }}</span>
                    @endif
                </div>

                <div class="flex flex-1 flex-col p-5">
                    <div class="mb-2 flex flex-wrap gap-1.5">
                        @foreach ($course->categories as $cat)
                            <span class="rounded-sm bg-black/[0.06] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-black/55">{{ $cat->name }}</span>
                        @endforeach
                    </div>

                    <h2 class="font-display text-xl font-bold uppercase leading-tight tracking-wide text-ink group-hover:text-brand transition">
                        {{ $course->title }}
                    </h2>

                    @if ($instructors !== '')
                        <div class="mt-2 flex items-center gap-1.5 text-sm text-black/55">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0v.75h-15v-.75z"/>
                            </svg>
                            <span class="truncate">{{ $instructors }}</span>
                        </div>
                    @endif

                    <div class="mt-auto flex items-center justify-between pt-4">
                        <span class="font-display text-sm font-bold uppercase tracking-wider text-gold">
                            {{ rtrim(rtrim((string) $course->hours, '0'), '.') }} hrs
                        </span>
                        <span class="text-xs uppercase tracking-widest text-black/35">
                            {{ $course->requires_approval ? 'Approval needed' : 'Instant credit' }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if (count($courses) === 0)
        <div class="rounded-xl border-2 border-dashed border-black/15 py-20 text-center text-black/45">
            No published courses in this category yet.
        </div>
    @endif
</div>
@endsection
