@extends('layouts.app')
@section('title', 'Search History')

@section('content')

<x-page-header num="—" title="Search History" :meta="count($history) . ' recent'" />

@if($history->isEmpty())
    <div class="border border-white/8 p-16 text-center">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-4">Empty</div>
        <p class="font-serif italic text-2xl text-paper/60">No searches yet.</p>
    </div>
@else
    <div class="border border-white/8 divide-y divide-white/8">
        @foreach($history as $i => $h)
            <div class="flex items-center justify-between p-6 hover:bg-ink-2 transition-colors group" data-reveal="left">
                <div class="flex items-center gap-6">
                    <span class="font-mono text-[10px] text-paper/30 tabular-nums">
                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        <div class="font-display text-lg uppercase tracking-tight">{{ $h->query }}</div>
                        <div class="font-mono text-[10px] text-paper/40 mt-1 tracking-wider">
                            {{ $h->created_at->format('d.m.Y · H:i') }}
                        </div>
                    </div>
                </div>
                @if($h->resolved_user_id)
                    <a href="{{ route('dashboard.overview', $h->resolved_user_id) }}"
                       class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 hover:text-acid transition-colors">
                        View →
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@endif

<div class="mt-12">
    <a href="{{ route('home') }}"
       class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 hover:text-acid transition-colors">
        ← Back to search
    </a>
</div>

@endsection