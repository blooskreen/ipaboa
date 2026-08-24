@extends('layouts.public')
@section('title', 'Dashboard')

@section('content')

@include('partials.member-nav')

{{-- Player-card hero --}}
<section class="relative overflow-hidden bg-ink">

    {{-- banner: uploaded image if present, brand gradient otherwise --}}
    @if ($user->bannerUrl())
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $user->bannerUrl() }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-ink via-ink/85 to-ink/40"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-brand-dark via-brand to-ink"></div>
    @endif

    {{-- diagonal jersey stripes --}}
    <div class="absolute inset-0 opacity-[0.06]"
         style="background-image: repeating-linear-gradient(115deg, #C9A227 0 3px, transparent 3px 18px);"></div>
    <div class="absolute -right-16 -top-24 h-80 w-80 rounded-full border-[2.5rem] border-gold/10"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-8 sm:pt-14 sm:pb-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

            {{-- portrait --}}
            <div class="shrink-0">
                @if ($user->photoUrl())
                    <img src="{{ $user->photoUrl() }}" alt=""
                         class="h-36 w-36 rounded-lg object-cover ring-4 ring-gold shadow-2xl">
                @else
                    <div class="flex h-36 w-36 items-center justify-center rounded-lg bg-black/50 ring-4 ring-gold shadow-2xl">
                        <span class="font-display text-6xl font-bold text-gold">{{ $user->initials() }}</span>
                    </div>
                @endif
            </div>

            {{-- identity --}}
            <div class="min-w-0 flex-1">
                <p class="font-display text-[11px] font-semibold uppercase tracking-[0.35em] text-gold">
                    {{ $training ? 'Official Profile' : 'Member Profile' }}
                </p>

                <h1 class="mt-1 font-display text-5xl sm:text-6xl font-bold uppercase leading-[0.9] tracking-tight text-white">
                    {{ $user->name }}
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @foreach ($user->getRoleNames() as $role)
                        <span class="rounded-sm bg-gold px-2.5 py-1 font-display text-[11px] font-bold uppercase tracking-widest text-ink">{{ $role }}</span>
                    @endforeach
                    @if ($user->classification)
                        <span class="rounded-sm border border-white/30 px-2.5 py-1 font-display text-[11px] font-bold uppercase tracking-widest text-white">{{ $user->classification }}</span>
                    @endif
                </div>
            </div>

            <div class="shrink-0">
                <a href="/profile" class="inline-block rounded-md border-2 border-white/35 px-6 py-3 font-display text-sm font-bold uppercase tracking-wider text-white hover:border-gold hover:text-gold transition">
                    Edit profile
                </a>
            </div>
        </div>
    </div>

    {{-- stat line --}}
    <div class="relative border-t border-white/10 bg-black/45">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <dl class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 divide-x divide-white/10">
                @foreach ($stats as $stat)
                    <div class="px-4 py-5 text-center">
                        <dt class="font-display text-2xl sm:text-3xl font-bold text-gold">{{ $stat['value'] }}</dt>
                        <dd class="mt-0.5 text-[10px] uppercase tracking-[0.15em] text-white/45">{{ $stat['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-8 pb-20">

@if (session('status'))
    <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc space-y-1 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ---------------------------------------------------------- media strip --}}
<section class="mb-8">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">My {{ \App\Support\Brand::GALLERY_NAME }}</h2>
        <button type="button" id="mediaToggle"
                class="rounded-md bg-brand px-4 py-2 font-display text-xs font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
            Add photo or video
        </button>
    </div>

    <div id="mediaPanel" class="mb-4 hidden rounded-xl border-2 border-brand/30 bg-brand/[0.03] p-5">
        <form method="POST" action="{{ route('media.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="flex flex-wrap gap-5">
                <label class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <input type="radio" name="type" value="image" checked class="media-type text-brand focus:ring-brand/30">
                    Photo upload
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-ink">
                    <input type="radio" name="type" value="video" class="media-type text-brand focus:ring-brand/30">
                    Video link
                </label>
            </div>

            <div id="fieldImage">
                <label for="file" class="block text-sm font-semibold text-ink mb-1.5">Photo</label>
                <input id="file" name="file" type="file" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded-md border border-black/20 px-4 py-2.5 text-sm">
                <p class="mt-1.5 text-xs text-black/50">JPG, PNG or WebP, up to 8 MB. Large photos are resized automatically.</p>
            </div>

            <div id="fieldVideo" class="hidden">
                <label for="video_url" class="block text-sm font-semibold text-ink mb-1.5">Video link</label>
                <input id="video_url" name="video_url" type="url" placeholder="https://www.youtube.com/watch?v=..."
                       class="w-full rounded-md border border-black/20 px-4 py-2.5">
                <p class="mt-1.5 text-xs text-black/50">YouTube or Vimeo. Videos are linked, never uploaded.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="caption" class="block text-sm font-semibold text-ink mb-1.5">Caption</label>
                    <input id="caption" name="caption" type="text" maxlength="255" value="{{ old('caption') }}"
                           class="w-full rounded-md border border-black/20 px-4 py-2.5">
                </div>
                <div>
                    <label for="taken_on" class="block text-sm font-semibold text-ink mb-1.5">Date taken</label>
                    <input id="taken_on" name="taken_on" type="date" value="{{ old('taken_on') }}"
                           class="w-full rounded-md border border-black/20 px-4 py-2.5">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="rounded-md bg-gold px-6 py-2.5 font-display text-sm font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                    Add to gallery
                </button>
                <button type="button" id="mediaCancel"
                        class="rounded-md border border-black/20 px-6 py-2.5 font-display text-sm font-bold uppercase tracking-wider text-black/60 hover:text-ink transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <div class="flex gap-3 overflow-x-auto pb-2">
        @foreach ($media as $item)
            @php
                $thumb = $item->thumbUrl();
                $href  = $item->isVideo() ? ($item->video_url ?: '#') : ($item->url() ?: '#');
            @endphp
            <div class="group relative h-40 w-40 shrink-0 overflow-hidden rounded-lg border border-black/10 bg-black/5">
                <a href="{{ $href }}" target="_blank" rel="noopener" class="block h-full w-full">
                    <img src="{{ $thumb }}" alt="{{ $item->caption }}" class="h-full w-full object-cover">
                </a>

                @if ($item->isVideo())
                    <span class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-black/60">
                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6 4l10 6-10 6V4z"/></svg>
                        </span>
                    </span>
                @endif

                @if ($item->caption)
                    <span class="pointer-events-none absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-black/80 to-transparent px-2 pb-1.5 pt-6 text-[11px] text-white">
                        {{ $item->caption }}
                    </span>
                @endif

                <form method="POST" action="{{ route('media.destroy', $item) }}"
                      onsubmit="return confirm('Remove this from your gallery?');"
                      class="absolute right-1.5 top-1.5 opacity-0 transition group-hover:opacity-100">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Remove"
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-white hover:bg-brand-red">
                        &times;
                    </button>
                </form>
            </div>
        @endforeach

        @if (count($media) === 0)
            <div class="flex h-40 flex-1 items-center justify-center rounded-lg border-2 border-dashed border-black/15 px-6 text-sm text-black/45">
                No photos or videos yet. Use "Add photo or video" to start your gallery.
            </div>
        @endif
    </div>
</section>

@if ($training)
{{-- --------------------------------------------------------- stat boxes --}}
<div class="mb-8 grid gap-4 sm:grid-cols-3">
    @foreach ($trainingStats as $box)
        <div class="rounded-xl border border-black/10 bg-black/[0.02] p-5">
            <div class="font-display text-xs font-bold uppercase tracking-[0.15em] text-black/45">{{ $box['label'] }}</div>
            <div class="mt-1 font-display text-4xl font-bold text-brand">{{ $box['value'] }}</div>
            <div class="mt-0.5 text-xs text-black/40">{{ $box['sub'] }}</div>
        </div>
    @endforeach
</div>
@endif


@if (! $training)
    {{-- ---------------- Standard User: not yet an official ---------------- --}}
    <div class="rounded-xl border-2 border-gold bg-gold/5 p-8">
        <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-ink">Welcome to {{ \App\Support\Brand::ORG_SHORT }}</h2>
        <p class="mt-3 max-w-2xl text-black/65 leading-relaxed">
            Your account is active. Training courses, assessments and certificates unlock once
            leadership approves you as an official or enrolls you in the camper program.
            In the meantime you can set up your profile and take part in the community.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="/profile" class="rounded-md bg-brand px-6 py-3 font-display text-sm font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
                Complete your profile
            </a>
            <a href="mailto:campleadership@ipaboa.com" class="rounded-md border-2 border-brand px-6 py-3 font-display text-sm font-bold uppercase tracking-wider text-brand hover:bg-brand hover:text-white transition">
                Contact leadership
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @foreach ([
            [\App\Support\Brand::FEED_NAME, 'Your posts, questions and shoutouts appear here.'],
            ['My ' . \App\Support\Brand::GALLERY_NAME, 'Photos you submit to the association gallery.'],
            ['My Orders', 'Your store orders, status and tracking.'],
        ] as $card)
            <div class="rounded-xl border border-black/10 p-6">
                <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">{{ $card[0] }}</h2>
                <p class="mt-3 text-sm text-black/50">{{ $card[1] }}</p>
                <p class="mt-4 text-xs uppercase tracking-widest text-black/30">Nothing here yet</p>
            </div>
        @endforeach
    </div>
@endif

@if ($training)
    @php
        $pct     = max(0, min(100, (float) $percent)) / 100;
        $arcLen  = 251.33;                        // pi * r, r = 80
        $offset  = $arcLen * (1 - $pct);
        $angle   = deg2rad(180 + ($pct * 180));
        $needleX = 100 + (64 * cos($angle));
        $needleY = 100 + (64 * sin($angle));

        if ($percent >= 100)    { $gaugeColor = '#16a34a'; $gaugeWord = 'Requirement met'; }
        elseif ($percent >= 50) { $gaugeColor = '#C9A227'; $gaugeWord = 'On track'; }
        else                    { $gaugeColor = '#C8102E'; $gaugeWord = 'Behind pace'; }

        $remaining = max(0, $required - $hours);
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Hours gauge --}}
        <div class="rounded-xl border border-black/10 p-6">
            <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">Training Hours</h2>
            <p class="mt-0.5 text-xs uppercase tracking-widest text-black/45">{{ $season ?? 'No season set' }}</p>

            <svg viewBox="0 0 200 120" class="mx-auto mt-4 w-full max-w-[15rem]">
                <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="16" stroke-linecap="round"/>
                <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="{{ $gaugeColor }}" stroke-width="16"
                      stroke-linecap="round" stroke-dasharray="{{ $arcLen }}" stroke-dashoffset="{{ $offset }}"/>
                <line x1="100" y1="100" x2="{{ round($needleX, 2) }}" y2="{{ round($needleY, 2) }}"
                      stroke="#0D0D0D" stroke-width="3" stroke-linecap="round"/>
                <circle cx="100" cy="100" r="7" fill="#0D0D0D"/>
                <text x="20" y="117" font-size="10" fill="#9ca3af" text-anchor="middle">0</text>
                <text x="180" y="117" font-size="10" fill="#9ca3af" text-anchor="middle">{{ (int) $required }}</text>
            </svg>

            <div class="mt-2 text-center">
                <div class="font-display text-4xl font-bold text-ink">
                    {{ rtrim(rtrim(number_format($hours, 2), '0'), '.') }}<span class="text-xl text-black/35"> / {{ (int) $required }}</span>
                </div>
                <div class="mt-1 text-sm font-semibold" style="color: {{ $gaugeColor }}">{{ $gaugeWord }}</div>
                @if ($remaining > 0)
                    <div class="mt-1 text-xs text-black/50">{{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} hours remaining</div>
                @endif
            </div>
        </div>

        {{-- My Courses --}}
        <div class="rounded-xl border border-black/10 p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">My Courses</h2>
                <a href="/courses" class="text-sm font-semibold text-brand hover:text-brand-red">Browse courses</a>
            </div>

            @forelse ($completions as $c)
                @php
                    $statusColor = match ($c->status) {
                        'approved' => 'bg-green-100 text-green-800',
                        'pending'  => 'bg-amber-100 text-amber-800',
                        default    => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <div class="mt-4 flex items-center gap-4 border-t border-black/[0.07] pt-4 first:mt-3 first:border-0 first:pt-0">
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-semibold text-ink">{{ $c->course?->title ?? 'Course removed' }}</div>
                        <div class="mt-0.5 text-xs text-black/50">{{ $c->season }} &middot; {{ rtrim(rtrim((string) $c->hours_credited, '0'), '.') }} hrs</div>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $statusColor }}">
                        {{ \App\Models\CourseCompletion::STATUSES[$c->status] ?? $c->status }}
                    </span>
                </div>
            @empty
                <p class="mt-6 text-sm text-black/50">You are not enrolled in any courses yet.</p>
            @endforelse
        </div>

        {{-- My Assessments --}}
        <div class="rounded-xl border border-black/10 p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">My Assessments</h2>
                <a href="/quizzes" class="text-sm font-semibold text-brand hover:text-brand-red">Testing center</a>
            </div>

            @forelse ($attempts as $a)
                @php
                    if ($a->status === 'pending_review')  { $tone = 'bg-blue-100 text-blue-800';   $word = 'Awaiting grading'; }
                    elseif ($a->passed)                   { $tone = 'bg-green-100 text-green-800'; $word = 'Passed'; }
                    elseif ($a->status === 'in_progress') { $tone = 'bg-amber-100 text-amber-800'; $word = 'In progress'; }
                    else                                  { $tone = 'bg-red-100 text-red-800';     $word = 'Did not pass'; }
                @endphp
                <div class="mt-4 flex items-center gap-4 border-t border-black/[0.07] pt-4 first:mt-3 first:border-0 first:pt-0">
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-semibold text-ink">{{ $a->quiz?->title ?? 'Assessment removed' }}</div>
                        <div class="mt-0.5 text-xs text-black/50">
                            Attempt {{ $a->attempt_number }} &middot; {{ $a->submitted_at?->format('M j, Y') ?? 'not submitted' }}
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="font-display text-lg font-bold text-ink">{{ rtrim(rtrim((string) $a->percentage, '0'), '.') }}%</div>
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $tone }}">{{ $word }}</span>
                    </div>
                </div>
            @empty
                <p class="mt-6 text-sm text-black/50">You have not taken any assessments yet.</p>
            @endforelse
        </div>

        {{-- Certificates --}}
        <div class="rounded-xl border border-black/10 p-6">
            <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">My Certificates</h2>

            @forelse ($certificates as $cert)
                <a href="{{ route('certificates.download', $cert) }}" target="_blank"
                   class="mt-4 flex items-center gap-3 rounded-lg border border-black/10 p-3 hover:border-gold hover:bg-gold/5 transition">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-brand/10">
                        <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink">{{ $cert->title }}</div>
                        <div class="text-[11px] text-black/45">{{ $cert->issued_at->format('M j, Y') }} &middot; {{ $cert->serial }}</div>
                    </div>
                </a>
            @empty
                <p class="mt-6 text-sm text-black/50">Certificates appear here when you pass an assessment or complete a certified course.</p>
            @endforelse
        </div>

        {{-- Community cards, available to every member --}}
        @foreach ([
            [\App\Support\Brand::FEED_NAME, 'Your posts, questions and shoutouts.'],
            ['My ' . \App\Support\Brand::GALLERY_NAME, 'Photos you have submitted.'],
            ['My Orders', 'Store orders, status and tracking.'],
        ] as $card)
            <div class="rounded-xl border border-black/10 p-6">
                <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">{{ $card[0] }}</h2>
                <p class="mt-3 text-sm text-black/50">{{ $card[1] }}</p>
                <p class="mt-4 text-xs uppercase tracking-widest text-black/30">Nothing here yet</p>
            </div>
        @endforeach

        {{-- Past seasons --}}
        <div class="rounded-xl border border-black/10 p-6 lg:col-span-3">
            <h2 class="font-display text-lg font-bold uppercase tracking-wide text-ink">Past Seasons</h2>

            @if (count($history) === 0)
                <p class="mt-6 text-sm text-black/50">Your season history will build up here as hours are approved.</p>
            @endif

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($history as $row)
                    @php
                        $rowPct   = $row['required'] > 0 ? min(100, round($row['hours'] / $row['required'] * 100)) : 0;
                        $rowColor = $row['met'] ? '#16a34a' : '#C8102E';
                    @endphp
                    <div class="rounded-lg border border-black/10 p-4">
                        <div class="font-display text-sm font-bold uppercase tracking-wider text-black/60">{{ $row['season'] }}</div>
                        <div class="mt-1 font-display text-2xl font-bold text-ink">
                            {{ rtrim(rtrim(number_format($row['hours'], 2), '0'), '.') }}
                            <span class="text-base text-black/35">/ {{ (int) $row['required'] }}</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-black/10">
                            <div class="h-full rounded-full" style="width: {{ $rowPct }}%; background: {{ $rowColor }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endif

</div>

<script>
    (function () {
        var toggle = document.getElementById('mediaToggle');
        var cancel = document.getElementById('mediaCancel');
        var panel  = document.getElementById('mediaPanel');
        var img    = document.getElementById('fieldImage');
        var vid    = document.getElementById('fieldVideo');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', function () { panel.classList.toggle('hidden'); });
        if (cancel) cancel.addEventListener('click', function () { panel.classList.add('hidden'); });

        document.querySelectorAll('.media-type').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isVideo = this.value === 'video';
                img.classList.toggle('hidden', isVideo);
                vid.classList.toggle('hidden', !isVideo);
            });
        });
    })();
</script>

@endsection
