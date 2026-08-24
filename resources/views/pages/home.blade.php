@extends('layouts.public')

@section('title', 'Home')
@section('description', 'IPABOA trains, certifies and develops basketball officials for pro-am competition.')

@section('content')

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-brand">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-dark via-brand to-brand-blue"></div>
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full border-[3rem] border-gold/10"></div>
        <div class="absolute -left-32 bottom--20 h-80 w-80 rounded-full border-[2rem] border-white/5"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
            <div class="max-w-3xl">
                <p class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-gold mb-4">
                    International Pro-Am Basketball Officials Association
                </p>
                <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold uppercase leading-[0.95] tracking-tight text-white">
                    Officiate at a
                    <span class="text-gold">higher standard</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/80">
                    Structured training, live camps, rules certification and evaluation for
                    officials who take the craft seriously.
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="/register"
                       class="rounded-md bg-gold px-8 py-4 font-display text-base font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                        Become a member
                    </a>
                    <a href="#program"
                       class="rounded-md border-2 border-white/30 px-8 py-4 font-display text-base font-bold uppercase tracking-wider text-white hover:border-gold hover:text-gold transition">
                        See the program
                    </a>
                </div>
            </div>
        </div>

        <div class="relative border-t border-white/10 bg-ink/30">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <dl class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-white/10">
                    @foreach ([
                        ['8', 'Training hours per season'],
                        ['1', 'Year first-year program'],
                        ['5', 'Course categories'],
                        ['100%', 'Rules certification'],
                    ] as $stat)
                        <div class="px-4 py-8 text-center">
                            <dt class="font-display text-4xl font-bold text-gold">{{ $stat[0] }}</dt>
                            <dd class="mt-1 text-xs uppercase tracking-widest text-white/50">{{ $stat[1] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- Program --}}
    <section id="program" class="py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="font-display text-sm font-semibold uppercase tracking-[0.2em] text-brand-red">The program</p>
                <h2 class="mt-3 font-display text-4xl sm:text-5xl font-bold uppercase tracking-tight text-ink">
                    Everything an official needs, in one place
                </h2>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['Training &amp; Camps', 'Weekly meetings, scrimmages and live training camps. Every session counts toward your season requirement, tracked automatically.', 'brand'],
                    ['Rules Certification', 'Structured assessments with instant scoring, a live proctor view for in-person testing, and a certificate on every pass.', 'brand-red'],
                    ['Evaluation &amp; Ratings', 'Weighted evaluation across published criteria, play-by-play logging, and a classification you can actually work toward.', 'brand-blue'],
                ] as $card)
                    <div class="group relative rounded-xl border border-black/10 p-8 hover:border-gold hover:shadow-lg transition">
                        <div class="h-1.5 w-14 rounded-full bg-{{ $card[2] }} mb-6"></div>
                        <h3 class="font-display text-2xl font-bold uppercase tracking-wide text-ink">{!! $card[0] !!}</h3>
                        <p class="mt-4 text-[15px] leading-relaxed text-black/65">{{ $card[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- First year --}}
    <section class="py-24 bg-ink">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid gap-14 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="font-display text-sm font-semibold uppercase tracking-[0.2em] text-gold">New officials</p>
                <h2 class="mt-3 font-display text-4xl sm:text-5xl font-bold uppercase tracking-tight text-white">
                    The first-year program
                </h2>
                <p class="mt-6 text-lg leading-relaxed text-white/70">
                    First-year officials follow a structured roadmap: weekly sessions, mentored
                    scrimmages, rules testing and a transparent rating that shows exactly where
                    you stand and what to work on.
                </p>
                <a href="/register"
                   class="mt-8 inline-block rounded-md bg-gold px-8 py-4 font-display text-base font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                    Start your first year
                </a>
            </div>

            <div class="space-y-4">
                @foreach ([
                    ['Week 1', 'Orientation and mechanics'],
                    ['Ongoing', 'Weekly Zoom rules sessions'],
                    ['Camp', 'Live scrimmage evaluation'],
                    ['Season end', 'Certification and graduation'],
                ] as $i => $step)
                    <div class="flex gap-5 rounded-lg border border-white/10 bg-white/[0.03] p-5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gold font-display text-lg font-bold text-ink">
                            {{ $i + 1 }}
                        </div>
                        <div>
                            <div class="font-display text-xs font-bold uppercase tracking-widest text-gold">{{ $step[0] }}</div>
                            <div class="mt-1 text-white/85">{{ $step[1] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gold">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 flex flex-col sm:flex-row items-center justify-between gap-8">
            <div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold uppercase tracking-tight text-ink">
                    Ready to join?
                </h2>
                <p class="mt-2 text-ink/70">Registration is open. Get started in under two minutes.</p>
            </div>
            <a href="/register"
               class="shrink-0 rounded-md bg-ink px-10 py-4 font-display text-base font-bold uppercase tracking-wider text-gold hover:bg-ink-soft transition">
                Create your account
            </a>
        </div>
    </section>

@endsection
