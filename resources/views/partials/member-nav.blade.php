@php
    $u = auth()->user();

    $links = [['label' => 'Dashboard', 'href' => '/dashboard']];

    if ($u?->isTrainingMember()) {
        $links[] = ['label' => 'Courses',      'href' => '#'];
        $links[] = ['label' => 'Testing',      'href' => '#'];
        $links[] = ['label' => 'Certificates', 'href' => '#'];
    }

    $links[] = ['label' => \App\Support\Brand::FEED_NAME, 'href' => '#'];
    $links[] = ['label' => 'My Gallery',                  'href' => '#'];
    $links[] = ['label' => 'My Orders',                   'href' => '#'];
    $links[] = ['label' => 'My Profile',                  'href' => '#'];

    $current = request()->path();
@endphp

<div class="border-b border-black/10 bg-black/[0.02]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex gap-1 overflow-x-auto">
            @foreach ($links as $link)
                @php $active = trim($link['href'], '/') === $current; @endphp
                <a href="{{ $link['href'] }}"
                   class="whitespace-nowrap border-b-2 px-4 py-3 font-display text-sm font-semibold uppercase tracking-wider transition
                          {{ $active ? 'border-brand text-brand' : 'border-transparent text-black/50 hover:text-brand' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
