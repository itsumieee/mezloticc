@extends('layouts.app')
@section('title', 'Avatar')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="06" title="Avatar"
              :meta="'Currently wearing · ' . ($wearing ? count($wearing['assetIds'] ?? []) : 0) . ' items'">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

<div class="border border-white/8 p-8 md:p-16 flex items-center justify-center bg-ink-2/50" data-reveal="scale">
    @if($avatarUrl)
        <img src="{{ $avatarUrl }}" alt=""
             class="max-h-[600px] object-contain"
             style="filter: drop-shadow(0 20px 60px rgba(212,255,0,0.08));">
    @else
        <p class="font-serif italic text-2xl text-paper/50">Avatar rendering unavailable.</p>
    @endif
</div>

@if($wearing && !empty($wearing['assetIds']))
    <div class="mt-12">
        <div class="flex items-center gap-4 font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-5">
            <span>Equipment</span>
            <span class="h-px flex-1 bg-white/8"></span>
            <span>{{ count($wearing['assetIds']) }} items</span>
        </div>

        <div class="grid grid-cols-3 md:grid-cols-6 gap-px bg-white/8 border border-white/8" data-reveal="scale">
            @foreach($wearing['assetIds'] as $i => $assetId)
                <a href="https://www.roblox.com/catalog/{{ $assetId }}" target="_blank"
                   class="bg-ink p-5 hover:bg-ink-2 transition-colors group">
                    <div class="font-mono text-[10px] text-paper/30 mb-2">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="font-mono text-xs group-hover:text-acid transition-colors">#{{ $assetId }}</div>
                </a>
            @endforeach
        </div>
    </div>
@endif

@endsection