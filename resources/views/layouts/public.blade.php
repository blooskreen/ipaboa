<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IPABOA') &middot; International Pro-Am Basketball Officials Association</title>
    <meta name="description" content="@yield('description', 'Training, certification and development for basketball officials.')">
    <link rel="icon" href="/img/crest.png" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-ink antialiased">

@php
    // href '#' marks a page not built yet; it becomes a real path as we go.
    $nav = [
        ['label' => 'About',       'href' => '#'],
        ['label' => 'Training',    'href' => '#'],
        ['label' => 'Gallery',     'href' => '#'],
        ['label' => \App\Support\Brand::FEED_NAME, 'href' => '/showcase'],
        ['label' => 'Blog',        'href' => '#'],
        ['label' => 'Calendar',    'href' => '#'],
        ['label' => 'Careers',     'href' => '#'],
        ['label' => 'Contact',     'href' => '#'],
    ];
@endphp

<header class="sticky top-0 z-50 bg-ink border-b-2 border-gold">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-4">

            {{-- LOGO SLOT: replace this anchor's contents with
                 <img src="/img/logo.png" class="h-12 w-auto" alt="IPABOA">
                 once the header logo is uploaded. --}}
            <a href="/" class="flex items-center gap-3 shrink-0">
                <img src="/img/crest.png" alt="" class="h-12 w-12 object-contain">
                <span class="leading-none">
                    <span class="block font-display text-2xl font-bold tracking-wider text-gold">IPABOA</span>
                    <span class="hidden sm:block text-[10px] uppercase tracking-[0.18em] text-white/45">
                        Pro-Am Basketball Officials
                    </span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-6">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}"
                       class="font-display text-sm font-semibold uppercase tracking-wider text-white/75 hover:text-gold transition">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden lg:flex items-center gap-5 shrink-0 pl-2">
                @auth
                    <a href="/dashboard" class="font-display text-sm font-semibold uppercase tracking-wider text-white/75 hover:text-gold transition">
                        Dashboard
                    </a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="font-display text-sm font-semibold uppercase tracking-wider text-white/50 hover:text-brand-red transition">
                            Log out
                        </button>
                    </form>
                @endauth
                @guest
                    <a href="/login" class="font-display text-sm font-semibold uppercase tracking-wider text-white/75 hover:text-gold transition">
                        Log in
                    </a>
                    <a href="/register"
                       class="rounded-md bg-gold px-4 py-2 font-display text-sm font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                        Join
                    </a>
                @endguest
            </div>

            <button type="button" id="navToggle" aria-label="Menu" aria-expanded="false"
                    class="lg:hidden rounded-md p-2 text-white hover:bg-white/10">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div id="navMenu" class="hidden lg:hidden border-t border-white/10 bg-ink">
        <div class="mx-auto max-w-7xl px-4 py-4 space-y-1">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}"
                   class="block rounded-md px-3 py-2 font-display text-base font-semibold uppercase tracking-wide text-white/80 hover:bg-white/5 hover:text-gold">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <div class="pt-3 mt-3 border-t border-white/10 flex gap-3">
                @auth
                    <a href="/dashboard" class="flex-1 rounded-md bg-gold px-4 py-2 text-center font-display font-bold uppercase text-ink">Dashboard</a>
                @endauth
                @guest
                    <a href="/login" class="flex-1 rounded-md border border-white/25 px-4 py-2 text-center font-display font-bold uppercase text-white">Log in</a>
                    <a href="/register" class="flex-1 rounded-md bg-gold px-4 py-2 text-center font-display font-bold uppercase text-ink">Join</a>
                @endguest
            </div>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="bg-ink text-white/70">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="/img/crest.png" alt="" class="h-10 w-10 object-contain">
                    <span class="font-display text-xl font-bold tracking-wider text-gold">IPABOA</span>
                </div>
                <p class="text-sm leading-relaxed text-white/55">
                    International Pro-Am Basketball Officials Association. Training, certification
                    and development for basketball officials.
                </p>
            </div>

            <div>
                <h3 class="font-display text-sm font-bold uppercase tracking-widest text-white mb-4">Members</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="/dashboard" class="hover:text-gold transition">Dashboard</a></li>
                    <li><a href="/courses" class="hover:text-gold transition">Courses</a></li>
                    <li><a href="/quizzes" class="hover:text-gold transition">Testing Center</a></li>
                    <li><a href="/showcase" class="hover:text-gold transition">{{ \App\Support\Brand::FEED_NAME }}</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display text-sm font-bold uppercase tracking-widest text-white mb-4">Association</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-gold transition">About</a></li>
                    <li><a href="#" class="hover:text-gold transition">Calendar</a></li>
                    <li><a href="#" class="hover:text-gold transition">Blog</a></li>
                    <li><a href="#" class="hover:text-gold transition">Careers</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display text-sm font-bold uppercase tracking-widest text-white mb-4">Support</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="mailto:onlinesupport@ipaboa.com" class="hover:text-gold transition">onlinesupport@ipaboa.com</a></li>
                    <li><a href="mailto:storesupport@ipaboa.com" class="hover:text-gold transition">storesupport@ipaboa.com</a></li>
                    <li><a href="mailto:campleadership@ipaboa.com" class="hover:text-gold transition">campleadership@ipaboa.com</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/40">
            <p>&copy; {{ date('Y') }} International Pro-Am Basketball Officials Association. All rights reserved.</p>
            <div class="flex gap-5">
                <a href="#" class="hover:text-gold transition">Terms</a>
                <a href="#" class="hover:text-gold transition">Returns</a>
                <a href="#" class="hover:text-gold transition">Shopping Support</a>
            </div>
        </div>
    </div>
</footer>

{{-- Vanilla JS only on public pages. No Alpine outside Filament/Livewire. --}}
<script>
    (function () {
        var btn  = document.getElementById('navToggle');
        var menu = document.getElementById('navMenu');
        if (!btn || !menu) return;
        btn.addEventListener('click', function () {
            var open = menu.classList.toggle('hidden') === false;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    })();
</script>

</body>
</html>
