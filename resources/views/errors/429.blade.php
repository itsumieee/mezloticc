@extends('layouts.app')
@section('title', 'Too many requests')
@section('content')
<div class="min-h-[60vh] flex items-center">
    <div>
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-6">Error · 429</div>
        <h1 class="font-display text-[clamp(4rem,14vw,12rem)] leading-none tracking-tighter uppercase">
            Slow <span class="font-serif italic normal-case text-signal">down</span>
        </h1>
        <p class="mt-8 text-paper/60 max-w-md">
            You're searching too fast. Please wait {{ $seconds ?? 60 }} seconds.
        </p>
        <a href="{{ route('home') }}"
           class="inline-block mt-10 border border-white/15 hover:border-acid hover:text-acid
                  px-6 py-3 font-mono text-[10px] tracking-[0.25em] uppercase transition-colors">
            Back to Home →
        </a>
    </div>
</div>
@endsection