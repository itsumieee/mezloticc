@props(['snapshots'])

@if($snapshots->count() > 1)
    @php
        $latest = $snapshots->last();
        $previous = $snapshots->get($snapshots->count() - 2);
        $rapDelta = $latest->total_rap - $previous->total_rap;
        $limitedDelta = $latest->limited_count - $previous->limited_count;
    @endphp
    <section class="mb-8 rounded-lg border border-white/10 bg-white p-5" aria-labelledby="trend-title">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 id="trend-title" class="font-semibold text-paper">Recent change</h2>
            <span class="text-xs text-paper/50">{{ $snapshots->count() }} daily snapshots</span>
        </div>
        <div class="grid grid-cols-2 gap-5 md:grid-cols-4">
            @foreach([
                ['Total RAP', number_format($latest->total_rap), $rapDelta, true],
                ['Limited items', number_format($latest->limited_count), $limitedDelta, false],
                ['Wearing', number_format($latest->wearing_count), null, false],
                ['Bundles', number_format($latest->bundles_count), null, false],
            ] as [$label, $value, $delta, $formatted])
                <div>
                    <div class="text-xs text-paper/50">{{ $label }}</div>
                    <div class="mt-1 text-xl font-semibold tabular-nums text-paper">{{ $value }}</div>
                    @if($delta !== null && $delta !== 0)
                        <div class="mt-1 text-xs font-medium {{ $delta > 0 ? 'text-acid' : 'text-signal' }}">
                            {{ $delta > 0 ? '+' : '' }}{{ $formatted ? number_format($delta) : $delta }} vs previous snapshot
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif