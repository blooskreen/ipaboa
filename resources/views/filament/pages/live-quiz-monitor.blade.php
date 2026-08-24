<x-filament-panels::page>

    @php
        $tabs = [
            'live'    => 'Live progress',
            'ranking' => 'Final ranking',
            'review'  => 'Question review',
        ];
        $quiz = $this->quiz();
    @endphp

    <div style="margin-bottom:1.25rem;">
        <label for="quizPicker" style="display:block;font-size:.8125rem;font-weight:600;margin-bottom:.375rem;opacity:.75;">
            Quiz
        </label>
        <select id="quizPicker" wire:model.live="quizId"
                style="width:100%;max-width:32rem;padding:.5rem .75rem;border-radius:.5rem;
                       border:1px solid rgba(128,128,128,.35);background:transparent;color:inherit;">
            <option value="">-- Select a quiz --</option>
            @foreach ($this->quizOptions() as $id => $title)
                <option value="{{ $id }}">{{ $title }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
        @foreach ($tabs as $key => $tabLabel)
            @php
                $active = $mode === $key;
                $tabStyle = $active
                    ? 'padding:.5rem 1rem;border-radius:.5rem;border:1px solid #d4af37;background:#d4af37;color:#111;font-weight:600;cursor:pointer;'
                    : 'padding:.5rem 1rem;border-radius:.5rem;border:1px solid rgba(128,128,128,.35);background:transparent;color:inherit;font-weight:500;cursor:pointer;';
            @endphp
            <button type="button" wire:click="$set('mode', '{{ $key }}')" style="{{ $tabStyle }}">
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    {{-- ---------------------------------------------------------------- LIVE --}}
    @if ($mode === 'live')
        @php
            $rows   = $this->rows();
            $counts = $this->counts();
        @endphp

        <div wire:poll.5s>
            @if ($quiz)
                <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;">
                    <div style="flex:1;min-width:9rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                        <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;letter-spacing:.04em;">Taking now</div>
                        <div style="font-size:1.75rem;font-weight:700;color:#f59e0b;">{{ $counts['inProgress'] }}</div>
                    </div>
                    <div style="flex:1;min-width:9rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                        <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;letter-spacing:.04em;">Finished</div>
                        <div style="font-size:1.75rem;font-weight:700;color:#16a34a;">{{ $counts['finished'] }}</div>
                    </div>
                    <div style="flex:1;min-width:9rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                        <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;letter-spacing:.04em;">Questions</div>
                        <div style="font-size:1.75rem;font-weight:700;">{{ $quiz->questions->count() }}</div>
                    </div>
                    <div style="flex:1;min-width:9rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                        <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;letter-spacing:.04em;">Pass mark</div>
                        <div style="font-size:1.75rem;font-weight:700;">{{ $quiz->passing_percentage }}%</div>
                    </div>
                </div>
            @endif

            <div style="border:1px solid rgba(128,128,128,.25);border-radius:.75rem;overflow:hidden;">
                @foreach ($rows as $row)
                    <div style="display:flex;align-items:center;gap:1rem;padding:.875rem 1rem;border-bottom:1px solid rgba(128,128,128,.15);">
                        <div style="flex:0 0 14rem;min-width:0;">
                            <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $row['name'] }}</div>
                            <div style="font-size:.75rem;opacity:.6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $row['email'] }}</div>
                        </div>
                        <div style="flex:1;min-width:8rem;">
                            <div style="height:1.25rem;border-radius:.625rem;background:rgba(128,128,128,.2);overflow:hidden;">
                                <div style="height:100%;width:{{ $row['progress'] }}%;background:{{ $row['color'] }};border-radius:.625rem;transition:width .4s ease;"></div>
                            </div>
                        </div>
                        <div style="flex:0 0 4rem;text-align:right;font-variant-numeric:tabular-nums;font-weight:600;">{{ $row['progress'] }}%</div>
                        <div style="flex:0 0 11rem;text-align:right;font-size:.8125rem;">
                            <div style="font-weight:600;color:{{ $row['color'] }};">{{ $row['label'] }}</div>
                            <div style="opacity:.65;">{{ $row['score'] }}</div>
                        </div>
                        <div style="flex:0 0 4.5rem;text-align:right;font-size:.75rem;opacity:.6;">{{ $row['elapsed'] }}</div>
                    </div>
                @endforeach
            </div>

            @if (count($rows) === 0)
                <div style="padding:2.5rem 1rem;text-align:center;opacity:.6;">
                    Nobody has started this quiz in the last 12 hours.
                </div>
            @endif

            <div style="margin-top:.75rem;font-size:.75rem;opacity:.5;">
                Refreshing every 5 seconds. Last updated {{ now()->format('g:i:s a') }}.
            </div>
        </div>
    @endif

    {{-- ------------------------------------------------------------- RANKING --}}
    @if ($mode === 'ranking')
        @php
            $ranks   = $this->ranking();
            $summary = $this->summary();
        @endphp

        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;">
            <div style="flex:1;min-width:8rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Finished</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['count'] }}</div>
            </div>
            <div style="flex:1;min-width:8rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Average</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['average'] }}%</div>
            </div>
            <div style="flex:1;min-width:8rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Median</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['median'] }}%</div>
            </div>
            <div style="flex:1;min-width:8rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Pass rate</div>
                <div style="font-size:1.75rem;font-weight:700;color:#16a34a;">{{ $summary['passRate'] }}%</div>
            </div>
            <div style="flex:1;min-width:8rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Avg time</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['avgMinutes'] }}</div>
            </div>
        </div>

        <div style="border:1px solid rgba(128,128,128,.25);border-radius:.75rem;overflow:hidden;">
            @foreach ($ranks as $row)
                <div style="display:flex;align-items:center;gap:1rem;padding:.875rem 1rem;border-bottom:1px solid rgba(128,128,128,.15);">
                    <div style="flex:0 0 3rem;text-align:center;">
                        <div style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:50%;font-weight:700;background:{{ $row['medal'] }};border:1px solid rgba(128,128,128,.3);">
                            {{ $row['rank'] }}
                        </div>
                    </div>
                    <div style="flex:0 0 14rem;min-width:0;">
                        <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $row['name'] }}</div>
                        <div style="font-size:.75rem;opacity:.6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $row['email'] }}</div>
                    </div>
                    <div style="flex:1;min-width:8rem;">
                        <div style="height:1.25rem;border-radius:.625rem;background:rgba(128,128,128,.2);overflow:hidden;">
                            <div style="height:100%;width:{{ $row['width'] }}%;background:{{ $row['color'] }};border-radius:.625rem;"></div>
                        </div>
                    </div>
                    <div style="flex:0 0 5rem;text-align:right;font-weight:700;font-variant-numeric:tabular-nums;">{{ $row['pct'] }}%</div>
                    <div style="flex:0 0 6rem;text-align:right;font-size:.8125rem;opacity:.7;">{{ $row['score'] }}</div>
                    <div style="flex:0 0 9rem;text-align:right;font-size:.8125rem;font-weight:600;color:{{ $row['color'] }};">{{ $row['label'] }}</div>
                    <div style="flex:0 0 4.5rem;text-align:right;font-size:.75rem;opacity:.6;">{{ $row['minutes'] }}</div>
                </div>
            @endforeach
        </div>

        @if (count($ranks) === 0)
            <div style="padding:2.5rem 1rem;text-align:center;opacity:.6;">No finished attempts yet.</div>
        @endif
    @endif

    {{-- -------------------------------------------------------------- REVIEW --}}
    @if ($mode === 'review')
        @php $questions = $this->review(); @endphp

        <div style="margin-bottom:1rem;font-size:.8125rem;opacity:.7;">
            Hardest questions first. The bar shows the percentage who got it right.
        </div>

        @foreach ($questions as $q)
            <div style="border:1px solid rgba(128,128,128,.25);border-radius:.75rem;padding:1rem 1.125rem;margin-bottom:1rem;">
                <div style="display:flex;gap:1rem;align-items:flex-start;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;margin-bottom:.25rem;">{{ $q['prompt'] }}</div>
                        <div style="font-size:.75rem;opacity:.6;">
                            {{ $q['typeLabel'] }} &middot; {{ $q['points'] }} pt &middot; answer key: {{ $q['answerKey'] }}
                        </div>
                    </div>
                    <div style="flex:0 0 7rem;text-align:right;">
                        <div style="font-size:1.5rem;font-weight:700;color:{{ $q['rateColor'] }};">{{ $q['rate'] }}%</div>
                        <div style="font-size:.75rem;opacity:.6;">{{ $q['correct'] }} of {{ $q['total'] }} correct</div>
                    </div>
                </div>

                <div style="height:.5rem;border-radius:.25rem;background:rgba(128,128,128,.2);overflow:hidden;margin:.75rem 0;">
                    <div style="height:100%;width:{{ $q['width'] }}%;background:{{ $q['rateColor'] }};"></div>
                </div>

                @foreach ($q['choices'] as $choice)
                    <div style="display:flex;align-items:center;gap:.75rem;margin-top:.375rem;">
                        <div style="flex:0 0 5.25rem;color:{{ $choice['color'] }};font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.05em;">
                            {{ $choice['marker'] }}
                        </div>
                        <div style="flex:0 0 14rem;font-size:.8125rem;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $choice['text'] }}
                        </div>
                        <div style="flex:1;min-width:6rem;">
                            <div style="height:.75rem;border-radius:.375rem;background:rgba(128,128,128,.15);overflow:hidden;">
                                <div style="height:100%;width:{{ $choice['width'] }}%;background:{{ $choice['color'] }};opacity:.75;"></div>
                            </div>
                        </div>
                        <div style="flex:0 0 5.5rem;text-align:right;font-size:.75rem;opacity:.7;font-variant-numeric:tabular-nums;">
                            {{ $choice['count'] }} ({{ $choice['pct'] }}%)
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:.5rem;font-size:.75rem;opacity:.55;">{{ $q['blank'] }} left blank</div>
            </div>
        @endforeach

        @if (count($questions) === 0)
            <div style="padding:2.5rem 1rem;text-align:center;opacity:.6;">Select a quiz with finished attempts.</div>
        @endif
    @endif

</x-filament-panels::page>
