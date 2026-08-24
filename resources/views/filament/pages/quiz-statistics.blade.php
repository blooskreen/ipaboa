<x-filament-panels::page>

    @php
        $inputStyle = 'width:100%;padding:.5rem .75rem;border-radius:.5rem;border:1px solid rgba(128,128,128,.35);background:transparent;color:inherit;';
        $labelStyle = 'display:block;font-size:.75rem;font-weight:600;margin-bottom:.375rem;opacity:.7;text-transform:uppercase;letter-spacing:.04em;';
        $cardStyle  = 'flex:1;min-width:8rem;padding:.875rem 1rem;border:1px solid rgba(128,128,128,.25);border-radius:.625rem;';
    @endphp

    <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.5rem;">
        <div style="flex:2;min-width:16rem;">
            <label for="q" style="{{ $labelStyle }}">Quiz</label>
            <select id="q" wire:model.live="quizId" style="{{ $inputStyle }}">
                <option value="">-- Select a quiz --</option>
                @foreach ($this->quizOptions() as $id => $title)
                    <option value="{{ $id }}">{{ $title }}</option>
                @endforeach
            </select>
        </div>

        <div style="flex:1;min-width:10rem;">
            <label for="s" style="{{ $labelStyle }}">Season</label>
            <select id="s" wire:model.live="seasonId" style="{{ $inputStyle }}">
                <option value="">All time</option>
                @foreach ($this->seasonOptions() as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div style="flex:1;min-width:9rem;">
            <label for="f" style="{{ $labelStyle }}">From</label>
            <input id="f" type="date" wire:model.live="from" style="{{ $inputStyle }}">
        </div>

        <div style="flex:1;min-width:9rem;">
            <label for="t" style="{{ $labelStyle }}">To</label>
            <input id="t" type="date" wire:model.live="to" style="{{ $inputStyle }}">
        </div>

        <div>
            <button type="button" wire:click="clearFilters"
                    style="padding:.5rem 1rem;border-radius:.5rem;border:1px solid rgba(128,128,128,.35);background:transparent;color:inherit;cursor:pointer;">
                Clear
            </button>
        </div>
    </div>

    @if ($quizId)
        @php
            $summary = $this->summary();
            $dist    = $this->distribution();
            $rows    = $this->breakdown();
        @endphp

        <div style="font-size:.8125rem;opacity:.65;margin-bottom:1rem;">
            Showing {{ $summary['count'] }} finished attempt(s) &middot; {{ $this->windowLabel() }}
        </div>

        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.75rem;">
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Attempts</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['count'] }}</div>
            </div>
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Average</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['average'] }}%</div>
            </div>
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Median</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['median'] }}%</div>
            </div>
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Pass rate</div>
                <div style="font-size:1.75rem;font-weight:700;color:#16a34a;">{{ $summary['passRate'] }}%</div>
            </div>
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">High</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['high'] }}%</div>
            </div>
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Low</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['low'] }}%</div>
            </div>
            <div style="{{ $cardStyle }}">
                <div style="font-size:.75rem;opacity:.65;text-transform:uppercase;">Avg time</div>
                <div style="font-size:1.75rem;font-weight:700;">{{ $summary['avgMinutes'] }}</div>
            </div>
        </div>

        <div style="border:1px solid rgba(128,128,128,.25);border-radius:.75rem;padding:1.125rem;margin-bottom:1.75rem;">
            <div style="font-weight:600;margin-bottom:1rem;">Score distribution</div>
            <div style="display:flex;align-items:flex-end;gap:.5rem;height:9rem;">
                @foreach ($dist as $bucket)
                    <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;height:100%;">
                        <div style="font-size:.6875rem;opacity:.7;margin-bottom:.25rem;">{{ $bucket['count'] }}</div>
                        <div style="width:100%;height:{{ $bucket['height'] }}%;min-height:2px;background:#4B2E83;border-radius:.25rem .25rem 0 0;"></div>
                    </div>
                @endforeach
            </div>
            <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                @foreach ($dist as $bucket)
                    <div style="flex:1;text-align:center;font-size:.625rem;opacity:.55;">{{ $bucket['label'] }}</div>
                @endforeach
            </div>
        </div>

        <div style="font-weight:600;margin-bottom:.75rem;">Question breakdown</div>
        <div style="font-size:.8125rem;opacity:.65;margin-bottom:1rem;">
            Hardest first. Bars under each question show how many people chose each option.
        </div>

        @foreach ($rows as $q)
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

        @if (count($rows) === 0)
            <div style="padding:2.5rem 1rem;text-align:center;opacity:.6;">No finished attempts in this window.</div>
        @endif
    @endif

    @if (! $quizId)
        <div style="padding:3rem 1rem;text-align:center;opacity:.6;">Select a quiz to see its statistics.</div>
    @endif

</x-filament-panels::page>
