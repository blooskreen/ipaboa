@extends('layouts.public')
@section('title', $quiz->title)

@section('content')
@php
    $total    = $questions->count();
    $answered = count(array_filter($answers, fn ($v) => $v !== null && trim((string) $v) !== ''));
@endphp

<div class="sticky top-20 z-40 border-b border-black/10 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center gap-4">
            <div class="min-w-0 flex-1">
                <div class="truncate font-display text-sm font-bold uppercase tracking-wider text-ink">{{ $quiz->title }}</div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-black/10">
                    <div id="progressBar" class="h-full rounded-full bg-brand transition-all duration-300"
                         style="width: {{ $total ? round($answered / $total * 100) : 0 }}%"></div>
                </div>
            </div>
            <div class="shrink-0 text-right">
                <div id="progressText" class="font-display text-sm font-bold text-ink">{{ $answered }} / {{ $total }}</div>
                @if ($deadline)
                    <div id="timer" class="font-display text-sm font-bold text-brand-red" data-deadline="{{ $deadline->timestamp }}">--:--</div>
                @else
                    <div class="text-[11px] uppercase tracking-widest text-black/35">Untimed</div>
                @endif
            </div>
            <div class="shrink-0">
                <span id="saveState" class="text-[11px] uppercase tracking-widest text-black/35">Saved</span>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">

    @if ($quiz->instructions)
        <div class="mb-8 rounded-lg border border-brand/25 bg-brand/[0.04] px-5 py-4 text-sm text-black/75">
            {{ $quiz->instructions }}
        </div>
    @endif

    <form method="POST" action="{{ route('quizzes.submit', $attempt) }}" id="quizForm"
          onsubmit="return confirm('Submit your answers? You cannot change them afterwards.');">
        @csrf

        @foreach ($questions as $i => $q)
            @php $given = $answers[$q->id] ?? ($answers[(string) $q->id] ?? ''); @endphp

            <div class="mb-6 rounded-xl border border-black/10 p-6" data-question="{{ $q->id }}">
                <div class="flex gap-4">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-ink font-display text-sm font-bold text-gold">
                        {{ $i + 1 }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold leading-relaxed text-ink">{{ $q->prompt }}</p>
                        <p class="mt-1 text-[11px] uppercase tracking-widest text-black/35">
                            {{ $q->points }} point{{ $q->points === 1 ? '' : 's' }}
                        </p>

                        <div class="mt-4 space-y-2">
                            @foreach ((array) $q->options as $option)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-black/15 px-4 py-3 transition hover:border-brand hover:bg-brand/[0.03]">
                                    <input type="radio" name="q{{ $q->id }}" value="{{ $option }}"
                                           data-qid="{{ $q->id }}" class="answer-radio text-brand focus:ring-brand/30"
                                           @checked((string) $given === (string) $option)>
                                    <span class="text-[15px] text-ink">{{ $option }}</span>
                                </label>
                            @endforeach

                            @if ($q->type === 'short')
                                <textarea data-qid="{{ $q->id }}" rows="4"
                                          class="answer-text w-full rounded-lg border border-black/20 px-4 py-3 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                                          placeholder="Type your answer">{{ $given }}</textarea>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="sticky bottom-0 -mx-4 border-t border-black/10 bg-white/95 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-b-xl">
            <button type="submit"
                    class="w-full rounded-md bg-gold px-8 py-4 font-display text-base font-bold uppercase tracking-wider text-ink hover:bg-gold-soft transition">
                Submit assessment
            </button>
            <p class="mt-2 text-center text-xs text-black/45">Your answers save automatically as you go.</p>
        </div>
    </form>
</div>

<script>
(function () {
    var url   = "{{ route('quizzes.answer', $attempt) }}";
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var bar   = document.getElementById('progressBar');
    var text  = document.getElementById('progressText');
    var state = document.getElementById('saveState');
    var timer = document.getElementById('timer');
    var form  = document.getElementById('quizForm');

    function flag(msg, color) {
        state.textContent = msg;
        state.style.color = color;
    }

    function save(qid, value) {
        flag('Saving...', '#C9A227');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ question_id: qid, value: value })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.ok) { flag('Not saved', '#C8102E'); return; }
            var pct = d.total ? Math.round(d.answered / d.total * 100) : 0;
            bar.style.width = pct + '%';
            text.textContent = d.answered + ' / ' + d.total;
            flag('Saved', 'rgba(0,0,0,0.35)');
            if (d.expired) { form.submit(); }
        })
        .catch(function () { flag('Offline', '#C8102E'); });
    }

    document.querySelectorAll('.answer-radio').forEach(function (el) {
        el.addEventListener('change', function () { save(this.dataset.qid, this.value); });
    });

    var timers = {};
    document.querySelectorAll('.answer-text').forEach(function (el) {
        el.addEventListener('input', function () {
            var self = this;
            clearTimeout(timers[self.dataset.qid]);
            flag('Typing...', '#C9A227');
            timers[self.dataset.qid] = setTimeout(function () {
                save(self.dataset.qid, self.value);
            }, 700);
        });
    });

    if (timer) {
        var deadline = parseInt(timer.dataset.deadline, 10) * 1000;
        setInterval(function () {
            var left = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
            var m = Math.floor(left / 60), s = left % 60;
            timer.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            if (left <= 0) { form.submit(); }
        }, 1000);
    }
})();
</script>
@endsection
