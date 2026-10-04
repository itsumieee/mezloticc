@extends('layouts.app')
@section('title', 'Statistics')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="08" title="Statistics" :meta="'Inventory distribution'">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

@if(!empty($statsErrors))
    <div class="border-l-2 border-signal pl-4 py-2 mb-8" role="status">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-1">Partial data</div>
        <p class="text-sm text-paper/70">
            Some Roblox categories could not be read: {{ implode(', ', array_keys($statsErrors)) }}.
            A zero may mean unavailable data rather than an empty category.
        </p>
    </div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-white/8 border border-white/8 mb-12" data-reveal>
    @foreach([
        ['01', 'Limited', $stats['limited'] ?? 0],
        ['02', 'Wearing', $stats['wearing'] ?? 0],
        ['03', 'Animations', $stats['animations'] ?? 0],
        ['04', 'Bundles', $stats['bundles'] ?? 0],
        ['05', 'Accessories', $stats['accessories'] ?? 0],
        ['06', 'Faces', $stats['faces'] ?? 0],
        ['07', 'Shirts', $stats['shirts'] ?? 0],
        ['08', 'Pants', $stats['pants'] ?? 0],
    ] as [$num, $label, $value])
        <div class="bg-ink p-6 group hover:bg-ink-2 transition-colors">
            <div class="flex items-start justify-between mb-6">
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40">{{ $label }}</div>
                <div class="font-mono text-[9px] text-paper/20">{{ $num }}</div>
            </div>
            <div class="font-display text-4xl md:text-5xl tracking-tighter tabular-nums text-acid leading-none">
                {{ $value }}
            </div>
            <div class="mt-6 h-px bg-white/10 relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-0 bg-acid group-hover:w-full transition-all duration-500"></div>
            </div>
        </div>
    @endforeach
</div>

<div class="grid md:grid-cols-2 gap-px bg-white/8 border border-white/8" data-reveal>
    <div class="bg-ink p-8 md:p-12">
        <div class="flex items-center gap-4 font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-8">
            <span>Composition</span>
            <span class="h-px flex-1 bg-white/8"></span>
            <span>Doughnut</span>
        </div>
        <div class="max-w-sm mx-auto">
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <div class="bg-ink p-8 md:p-12">
        <div class="flex items-center gap-4 font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-8">
            <span>Volume</span>
            <span class="h-px flex-1 bg-white/8"></span>
            <span>Bar</span>
        </div>
        <div>
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const labels = @json($chartLabels ?? []);
    const values = @json($chartValues ?? []);
    window.renderDonutChart('donutChart', labels, values);
    window.renderBarChart('barChart', labels, values);
});
</script>

@endsection