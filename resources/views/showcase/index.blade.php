@extends('layouts.public')
@section('title', \App\Support\Brand::FEED_NAME)

@section('content')
@auth
    @include('partials.member-nav')
@endauth

@php
    $emoji = ['👍','👏','🔥','💪','🏀','🎯','🙌','😀','😂','😅','🤔','😮','😎','🥇','🏆','📣','📅','⏰','✅','❌','⚠️','📝','🎓','🚗','✈️','🍀','💯','🙏','❤️','⭐','🔴','🟡','🟢','🔵','⚫','⚪','🥶','🤝','👀','🎉'];
    $me    = auth()->user();
@endphp

<div class="mx-auto max-w-2xl px-4 sm:px-6 py-8">

    <div class="mb-6">
        <h1 class="font-display text-4xl font-bold uppercase tracking-tight text-ink">{{ \App\Support\Brand::FEED_NAME }}</h1>
        <p class="mt-1 text-black/55">Questions, announcements, recaps and shoutouts from the association.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-5 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    {{-- category filter --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @php $allOn = $category === null; @endphp
        <a href="{{ route('showcase.index') }}"
           class="rounded-full border px-3.5 py-1.5 font-display text-[11px] font-bold uppercase tracking-wider transition {{ $allOn ? 'border-ink bg-ink text-white' : 'border-black/20 text-black/55 hover:border-ink' }}">
            All
        </a>
        @foreach (\App\Models\Post::CATEGORIES as $slug => $meta)
            @php
                $on = $category === $slug;
                $style = $on ? 'background:' . $meta[1] . ';border-color:' . $meta[1] . ';color:#fff;' : 'border-color:' . $meta[1] . '33;color:' . $meta[1] . ';';
            @endphp
            <a href="{{ route('showcase.index', ['category' => $slug]) }}"
               class="rounded-full border px-3.5 py-1.5 font-display text-[11px] font-bold uppercase tracking-wider transition"
               style="{{ $style }}">
                {{ $meta[0] }}
            </a>
        @endforeach
    </div>

    {{-- composer --}}
    @auth
    <div class="mb-8 rounded-xl border border-black/10 p-5">
        <form method="POST" action="{{ route('showcase.store') }}" enctype="multipart/form-data">
            @csrf

            <textarea id="composerBody" name="body" rows="3" maxlength="5000"
                      placeholder="Share something with the association..."
                      class="w-full resize-y rounded-lg border border-black/15 px-4 py-3 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('body') }}</textarea>

            <div class="mt-2 flex flex-wrap items-center gap-1 border-b border-black/[0.07] pb-2">
                <button type="button" class="fmt rounded px-2.5 py-1 text-sm font-bold hover:bg-black/5" data-before="**" data-after="**" title="Bold">B</button>
                <button type="button" class="fmt rounded px-2.5 py-1 text-sm italic hover:bg-black/5" data-before="*" data-after="*" title="Italic">I</button>
                <button type="button" class="fmt rounded px-2.5 py-1 text-sm line-through hover:bg-black/5" data-before="~~" data-after="~~" title="Strikethrough">S</button>
                <button type="button" class="fmt rounded px-2.5 py-1 font-mono text-sm hover:bg-black/5" data-before="`" data-after="`" title="Code">&lt;/&gt;</button>
                <button type="button" class="fmt rounded px-2.5 py-1 text-sm hover:bg-black/5" data-before="- " data-after="" title="Bullet">&bull;</button>
                <button type="button" id="linkBtn" class="rounded px-2.5 py-1 text-sm hover:bg-black/5" title="Insert link">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                    </svg>
                </button>
                <span class="ml-auto text-[11px] text-black/35">**bold** &nbsp; *italic* &nbsp; [text](link)</span>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" id="emojiBtn"
                        class="rounded-md border border-black/15 px-3 py-1.5 text-sm hover:border-brand" title="Emoji">😀</button>

                <select name="category" class="rounded-md border border-black/15 px-3 py-1.5 text-sm">
                    <option value="">No category</option>
                    @foreach (\App\Models\Post::CATEGORIES as $slug => $meta)
                        <option value="{{ $slug }}" @selected(old('category') === $slug)>{{ $meta[0] }}</option>
                    @endforeach
                </select>

                <select name="feeling" class="rounded-md border border-black/15 px-3 py-1.5 text-sm">
                    <option value="">No feeling</option>
                    @foreach (\App\Models\Post::FEELINGS as $slug => $word)
                        <option value="{{ $slug }}" @selected(old('feeling') === $slug)>{{ \App\Models\Post::FEELING_EMOJI[$slug] ?? '' }} {{ $word }}</option>
                    @endforeach
                </select>

                <button type="button" id="pollBtn"
                        class="rounded-md border border-black/15 px-3 py-1.5 text-sm hover:border-brand">Poll</button>

                <label class="cursor-pointer rounded-md border border-black/15 px-3 py-1.5 text-sm hover:border-brand">
                    Photos
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="hidden" id="imageInput">
                </label>
                <span id="imageCount" class="text-xs text-black/45"></span>
            </div>

            <div id="emojiPanel" class="mt-3 hidden rounded-lg border border-black/10 p-3">
                <div class="flex flex-wrap gap-1">
                    @foreach ($emoji as $e)
                        <button type="button" class="emoji-pick rounded px-1.5 py-1 text-lg hover:bg-black/5" data-emoji="{{ $e }}">{{ $e }}</button>
                    @endforeach
                </div>
            </div>

            <div id="pollPanel" class="mt-3 hidden rounded-lg border border-black/10 p-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-black/45">Poll options (2 to 4)</p>
                <div class="space-y-2">
                    @for ($i = 0; $i < 4; $i++)
                        <input type="text" name="poll_options[]" maxlength="120" placeholder="Option {{ $i + 1 }}"
                               class="w-full rounded-md border border-black/15 px-3 py-2 text-sm">
                    @endfor
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="rounded-md bg-brand px-6 py-2.5 font-display text-sm font-bold uppercase tracking-wider text-white hover:bg-brand-dark transition">
                    Post
                </button>
            </div>
        </form>
    </div>
    @endauth

    @guest
        <div class="mb-8 rounded-xl border-2 border-gold bg-gold/5 px-5 py-4 text-sm text-ink">
            <a href="/login" class="font-semibold text-brand hover:underline">Log in</a>
            or <a href="/register" class="font-semibold text-brand hover:underline">join</a>
            to post, comment and vote.
        </div>
    @endguest

    {{-- feed --}}
    <div class="space-y-6">
        @foreach ($posts as $post)
            @php
                $author     = $post->user;
                $avatar     = $author?->photoUrl();
                $catLabel   = $post->categoryLabel();
                $catColor   = $post->categoryColor();
                $feeling    = $post->feelingLabel();
                $imgs       = $post->images;
                $imgCount   = $imgs->count();
                $gridClass  = $imgCount === 1 ? 'grid-cols-1' : ($imgCount === 2 ? 'grid-cols-2' : 'grid-cols-2');
                $liked      = (bool) ($post->liked ?? false);
                $likeTone   = $liked ? 'text-brand-red font-semibold' : 'text-black/55';
                $canDelete  = $canModerate || ($me && $post->user_id === $me->getKey());
                $pollTotal  = $post->hasPoll() ? $post->pollVotes()->count() : 0;
                $myVote     = $me ? optional($post->pollVotes->first())->post_poll_option_id : null;
                $shownComments = $post->comments->take(-5);
                $hiddenCount   = max(0, $post->comments_count - $shownComments->count());
            @endphp

            <article class="rounded-xl border border-black/10 p-5">

                <div class="flex items-start gap-3">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover">
                    @endif
                    @if (! $avatar)
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-ink">
                            <span class="font-display text-sm font-bold text-gold">{{ $author?->initials() ?? '?' }}</span>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="font-semibold text-ink">{{ $author?->name ?? 'Removed member' }}</span>
                            @if ($feeling)
                                <span class="text-sm text-black/55">is feeling {{ $feeling }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-black/45">
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            @if ($catLabel)
                                <span class="rounded-full px-2 py-0.5 font-bold uppercase tracking-wider text-white" style="background: {{ $catColor }}">{{ $catLabel }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($canDelete)
                        <form method="POST" action="{{ route('showcase.destroy', $post) }}"
                              onsubmit="return confirm('Delete this post?');" class="shrink-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-black/30 hover:text-brand-red" title="Delete">&times;</button>
                        </form>
                    @endif
                </div>

                @if ($post->body)
                    <div class="mt-3 text-[15px] leading-relaxed text-ink">{!! \App\Support\PostText::render($post->body) !!}</div>
                @endif

                @if ($imgCount > 0)
                    <div class="mt-3 grid {{ $gridClass }} gap-1.5 overflow-hidden rounded-lg">
                        @foreach ($imgs as $img)
                            <a href="{{ $img->url() }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ $img->thumbUrl() }}" alt="" class="h-full w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($post->hasPoll())
                    <div class="mt-4 space-y-2">
                        @foreach ($post->pollOptions as $opt)
                            @php
                                $count = $opt->votes->count();
                                $pct   = $pollTotal > 0 ? round($count / $pollTotal * 100) : 0;
                                $mine  = $myVote === $opt->id;
                                $ring  = $mine ? 'ring-2 ring-gold' : '';
                            @endphp
                            <form method="POST" action="{{ route('showcase.vote', $post) }}">
                                @csrf
                                <input type="hidden" name="option_id" value="{{ $opt->id }}">
                                <button type="submit" class="relative block w-full overflow-hidden rounded-lg border border-black/15 px-4 py-2.5 text-left {{ $ring }}">
                                    <span class="absolute inset-y-0 left-0 bg-brand/15" style="width: {{ $pct }}%"></span>
                                    <span class="relative flex items-center justify-between text-sm">
                                        <span class="text-ink">{{ $opt->label }}</span>
                                        <span class="text-black/50">{{ $pct }}% ({{ $count }})</span>
                                    </span>
                                </button>
                            </form>
                        @endforeach
                        <p class="text-xs text-black/40">{{ $pollTotal }} vote{{ $pollTotal === 1 ? '' : 's' }}</p>
                    </div>
                @endif

                <div class="mt-4 flex items-center gap-5 border-t border-black/[0.07] pt-3">
                    <form method="POST" action="{{ route('showcase.like', $post) }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 text-sm {{ $likeTone }} hover:text-brand-red">
                            <svg class="h-4 w-4" fill="{{ $liked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                            </svg>
                            {{ $post->likes_count }}
                        </button>
                    </form>
                    <span class="flex items-center gap-1.5 text-sm text-black/55">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        {{ $post->comments_count }}
                    </span>
                </div>

                @if ($hiddenCount > 0)
                    <p class="mt-3 text-xs text-black/40">{{ $hiddenCount }} earlier comment{{ $hiddenCount === 1 ? '' : 's' }}</p>
                @endif

                @foreach ($shownComments as $comment)
                    @php
                        $cAvatar    = $comment->user?->photoUrl();
                        $canDelC    = $canModerate || ($me && $comment->user_id === $me->getKey());
                    @endphp
                    <div class="mt-3 flex items-start gap-2.5">
                        @if ($cAvatar)
                            <img src="{{ $cAvatar }}" alt="" class="h-8 w-8 shrink-0 rounded-full object-cover">
                        @endif
                        @if (! $cAvatar)
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black/10">
                                <span class="text-[10px] font-bold text-black/50">{{ $comment->user?->initials() ?? '?' }}</span>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1 rounded-lg bg-black/[0.035] px-3 py-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-ink">{{ $comment->user?->name ?? 'Removed member' }}</span>
                                <span class="text-[11px] text-black/35">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-0.5 text-sm text-black/75">{!! \App\Support\PostText::render($comment->body) !!}</div>
                        </div>
                        @if ($canDelC)
                            <form method="POST" action="{{ route('showcase.comment.destroy', $comment) }}"
                                  onsubmit="return confirm('Delete this comment?');" class="shrink-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-black/25 hover:text-brand-red" title="Delete">&times;</button>
                            </form>
                        @endif
                    </div>
                @endforeach

                @auth
                    <form method="POST" action="{{ route('showcase.comment', $post) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="body" maxlength="2000" placeholder="Write a comment"
                               class="flex-1 rounded-full border border-black/15 px-4 py-2 text-sm focus:border-brand focus:outline-none">
                        <button type="submit" class="rounded-full bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-brand transition">Send</button>
                    </form>
                @endauth
            </article>
        @endforeach
    </div>

    @if ($posts->isEmpty())
        <div class="rounded-xl border-2 border-dashed border-black/15 py-20 text-center text-black/45">
            Nothing posted yet.
        </div>
    @endif

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</div>

<script>
(function () {
    var body   = document.getElementById('composerBody');
    var eBtn   = document.getElementById('emojiBtn');
    var ePanel = document.getElementById('emojiPanel');
    var pBtn   = document.getElementById('pollBtn');
    var pPanel = document.getElementById('pollPanel');
    var files  = document.getElementById('imageInput');
    var count  = document.getElementById('imageCount');

    if (eBtn) eBtn.addEventListener('click', function () { ePanel.classList.toggle('hidden'); });
    if (pBtn) pBtn.addEventListener('click', function () { pPanel.classList.toggle('hidden'); });

    function wrap(before, after) {
        if (!body) return;
        var s = body.selectionStart, e = body.selectionEnd;
        var sel = body.value.slice(s, e);
        var placeholder = sel || 'text';
        body.value = body.value.slice(0, s) + before + placeholder + after + body.value.slice(e);
        body.focus();
        body.selectionStart = s + before.length;
        body.selectionEnd = s + before.length + placeholder.length;
    }

    document.querySelectorAll('.fmt').forEach(function (b) {
        b.addEventListener('click', function () {
            wrap(this.dataset.before, this.dataset.after);
        });
    });

    var linkBtn = document.getElementById('linkBtn');
    if (linkBtn) {
        linkBtn.addEventListener('click', function () {
            var url = window.prompt('Link address (must start with http:// or https://)');
            if (!url) return;
            if (!/^https?:\/\//i.test(url)) { alert('Links must start with http:// or https://'); return; }
            var s = body.selectionStart, e = body.selectionEnd;
            var label = body.value.slice(s, e) || 'link';
            var md = '[' + label + '](' + url + ')';
            body.value = body.value.slice(0, s) + md + body.value.slice(e);
            body.focus();
            body.selectionStart = body.selectionEnd = s + md.length;
        });
    }

    document.querySelectorAll('.emoji-pick').forEach(function (b) {
        b.addEventListener('click', function () {
            var e = this.dataset.emoji;
            var start = body.selectionStart, end = body.selectionEnd;
            body.value = body.value.slice(0, start) + e + body.value.slice(end);
            body.focus();
            body.selectionStart = body.selectionEnd = start + e.length;
        });
    });

    if (files) {
        files.addEventListener('change', function () {
            var n = this.files.length;
            count.textContent = n ? n + (n === 1 ? ' photo selected' : ' photos selected') : '';
        });
    }
})();
</script>
@endsection
