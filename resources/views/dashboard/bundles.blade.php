@extends('layouts.app')
@section('title', 'Bundles')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="07" title="Bundles"
              :meta="'Outfits · ' . ($total ?? '—') . ' items'">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

@if($error)
    <div class="border-l-2 border-signal pl-4 py-2 mb-8">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-1">Notice</div>
        <p class="text-sm text-paper/70">{{ $error }}</p>
    </div>
@endif

@if(empty($bundles))
    <div class="border border-white/8 p-16 text-center">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-4">Empty</div>
        <p class="font-serif italic text-2xl text-paper/60">Data unavailable.</p>
    </div>
@else
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-px bg-white/8 border border-white/8" data-reveal="scale">
        @foreach($bundles as $i => $b)
            <a href="https://www.roblox.com/bundles/{{ $b['id'] ?? '' }}" target="_blank"
               class="bg-ink p-8 hover:bg-ink-2 transition-colors group">
                <div class="font-mono text-[10px] text-paper/30 mb-4">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</div>
                <div class="font-display text-xl uppercase tracking-tight group-hover:text-acid transition-colors">
                    {{ $b['name'] ?? 'Unknown Bundle' }}
                </div>
                <div class="font-mono text-[10px] text-paper/40 mt-4 tracking-wider">
                    Bundle · {{ $b['id'] ?? '—' }}
                </div>
                <div class="mt-6 h-px bg-white/10 group-hover:bg-acid transition-colors"></div>
            </a>
        @endforeach
    </div>
@endif

@endsection