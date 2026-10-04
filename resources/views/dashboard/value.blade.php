@extends('layouts.app')
@section('title', 'Value')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="09" title="Value" :meta="'Recent average price · Public data'">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

@if($error)
    <div class="border-l-2 border-signal pl-4 py-2 mb-8" role="status">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-1">Partial data</div>
        <p class="text-sm text-paper/70">{{ $error }} RAP totals require a complete Limited-items scan.</p>
    </div>
@endif

@if(($rapData['item_count'] ?? 0) > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 gap-px bg-white/8 border border-white/8 mb-12" data-reveal="scale">
        <div class="bg-ink p-8">
            <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40">Total RAP</div>
            <div class="font-display text-5xl md:text-6xl tracking-tighter text-acid mt-5 tabular-nums">
                {{ number_format($rapData['total_rap']) }}
            </div>
        </div>
        <div class="bg-ink p-8">
            <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40">Average</div>
            <div class="font-display text-5xl md:text-6xl tracking-tighter mt-5 tabular-nums">
                {{ number_format($rapData['average_rap']) }}
            </div>
        </div>
        <div class="bg-ink p-8">
            <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40">Items w/ RAP</div>
            <div class="font-display text-5xl md:text-6xl tracking-tighter mt-5 tabular-nums">
                {{ number_format($rapData['item_count']) }}
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-px bg-white/8 border border-white/8" data-reveal="scale">
        @if($rapData['highest'])
            <div class="bg-ink p-8">
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-acid mb-4">↑ Highest</div>
                <div class="font-display text-2xl uppercase tracking-tight truncate">{{ $rapData['highest']['name'] }}</div>
                <div class="font-display text-4xl text-acid mt-4 tabular-nums">
                    {{ number_format($rapData['highest']['rap']) }}
                </div>
            </div>
        @endif
        @if($rapData['lowest'])
            <div class="bg-ink p-8">
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-4">↓ Lowest</div>
                <div class="font-display text-2xl uppercase tracking-tight truncate">{{ $rapData['lowest']['name'] }}</div>
                <div class="font-display text-4xl mt-4 tabular-nums">
                    {{ number_format($rapData['lowest']['rap']) }}
                </div>
            </div>
        @endif
    </div>

    @if(($rapData['item_count'] ?? 0) > 0)
        <div class="border border-white/8 p-8 md:p-12 mb-12" data-reveal>
            <div class="flex items-center gap-4 font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-8">
                <span>RAP Distribution</span>
                <span class="h-px flex-1 bg-white/8"></span>
                <span>Buckets</span>
            </div>
            <div class="h-[320px]">
                <canvas id="rapHistogram"></canvas>
            </div>
        </div>
    @endif

@else
    <div class="border border-white/8 p-16 text-center">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-4">Data unavailable</div>
        <p class="font-serif italic text-3xl text-paper/60">
            RAP data is not available for this account.
        </p>
        <p class="text-sm text-paper/40 mt-4 max-w-md mx-auto">
            The inventory may be private, or the items do not have public resale data.
        </p>
    </div>
@endif

@if($thirdParty)
    <section class="mt-12 border-t border-white/10 pt-8">
        <div class="mb-4 flex items-center gap-4 text-xs font-semibold uppercase tracking-wide text-paper/60">
            <span>Third-party data</span>
            <span class="h-px flex-1 bg-black/10"></span>
            <span>Rolimons · Unofficial</span>
        </div>
        <div class="overflow-x-auto rounded-lg border border-black/10 bg-white p-5">
            <pre class="text-xs leading-relaxed text-paper/70">{{ json_encode($thirdParty, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
        <p class="mt-3 text-xs text-paper/50">Rolimons is not affiliated with Roblox. Third-party data may differ from Roblox RAP and is not guaranteed to be accurate.</p>
    </section>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rapValues = @json(array_filter(array_column($items ?? [], 'recentAveragePrice')));
    if (rapValues.length) {
        window.renderHistogram('rapHistogram', rapValues);
    }
});
</script>
@endpush

@endsection