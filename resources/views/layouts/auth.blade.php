<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &middot; IPABOA</title>
    <link rel="icon" href="/img/crest.png" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-ink antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-2">

    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-brand p-12">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-dark via-brand to-brand-blue"></div>
        <div class="absolute -right-20 -top-20 h-96 w-96 rounded-full border-[3rem] border-gold/10"></div>

        <a href="/" class="relative flex items-center gap-3">
            <img src="/img/crest.png" alt="" class="h-12 w-12 object-contain">
            <span class="font-display text-2xl font-bold tracking-wider text-gold">IPABOA</span>
        </a>

        <div class="relative max-w-md">
            <h2 class="font-display text-4xl font-bold uppercase leading-tight tracking-tight text-white">
                Train. Certify. <span class="text-gold">Officiate.</span>
            </h2>
            <p class="mt-4 text-white/70 leading-relaxed">
                Track your training hours, take rules assessments, earn certificates and
                see exactly where you stand.
            </p>
        </div>

        <p class="relative text-xs uppercase tracking-[0.18em] text-white/40">
            International Pro-Am Basketball Officials Association
        </p>
    </div>

    <div class="flex items-center justify-center px-6 py-14 sm:px-12">
        <div class="w-full max-w-md">
            <a href="/" class="mb-10 flex items-center gap-3 lg:hidden">
                <img src="/img/crest.png" alt="" class="h-10 w-10 object-contain">
                <span class="font-display text-xl font-bold tracking-wider text-brand">IPABOA</span>
            </a>

            <h1 class="font-display text-3xl font-bold uppercase tracking-tight text-ink">@yield('heading')</h1>
            <p class="mt-2 text-sm text-black/55">@yield('subheading')</p>

            @if (session('status'))
                <div class="mt-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8">
                @yield('form')
            </div>

            <p class="mt-8 text-sm text-black/55">@yield('footer')</p>
        </div>
    </div>
</div>
</body>
</html>
