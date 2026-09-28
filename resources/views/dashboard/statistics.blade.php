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

<div class="statistics-mosaic" data-reveal="scale">
    @foreach([
        ['Limited', 'limited'],
        ['Wearing', 'wearing'],
        ['Animations', 'animations'],
        ['Bundles', 'bundles'],
        ['Accessories', 'accessories'],
        ['Faces', 'faces'],
        ['Shirts', 'shirts'],
        ['Pants', 'pants'],
        ['Hair', 'hair'],
    ] as [$label, $key])
        @php $value = $stats[$key] ?? 0; @endphp
        <div class="statistics-tile">
            <div class="statistics-label">{{ $label }}</div>
            <div class="statistics-value">
                {{ $value }}@if(!empty($hasMore[$key] ?? false))<span title="More results exist">+</span>@endif
            </div>
        </div>
    @endforeach
</div>

<p class="statistics-caption">
    Totals are counted across all pages returned by Roblox. Counts may take a moment on large inventories and are cached.
</p>

<div class="statistics-chart" data-reveal="scale">
    <div class="editorial-section-heading">
        <span class="section-index">01</span>
        <h2>Inventory composition</h2>
        <span class="section-rule"></span>
        <span class="section-note">DISTRIBUTION</span>
    </div>
    <div class="chart-canvas-wrap">
        <canvas id="categoryChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    window.renderCategoryChart?.(
        'categoryChart',
        @json($chartLabels ?? []),
        @json($chartValues ?? [])
    );
});
</script>

@endsection