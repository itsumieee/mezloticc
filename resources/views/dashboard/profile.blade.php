@extends('layouts.app')
@section('title', 'Profile')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="02" title="Profile" :meta="'@' . ($profile['name'] ?? '—') . ' · ' . $userId">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

<div class="grid md:grid-cols-12 gap-px bg-white/8 border border-white/8" data-reveal="scale">

    <div class="md:col-span-4 bg-ink p-8 flex items-center justify-center">
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="" class="w-full max-w-[280px] object-contain">
        @endif
    </div>

    <div class="md:col-span-8 bg-ink p-8 md:p-10 space-y-8">
        <div>
            <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-3">Display name</div>
            <div class="font-display text-3xl md:text-4xl uppercase tracking-tighter flex items-center gap-2">
                {{ $profile['displayName'] ?? '—' }}
                @if(!empty($profile['hasVerifiedBadge']))
                                        <span role="img" aria-label="Verified Roblox account" title="Verified Roblox account"
                                              class="roblox-verified-badge">
                                                <svg viewBox="0 0 24 24" class="size-full" fill="none" aria-hidden="true">
                                                        <path d="M12 2.25 15 3.4l3.2-.2 1.5 2.8 2.6 1.8-.7 3.1.7 3.1-2.6 1.8-1.5 2.8-3.2-.2-3 1.15-3-1.15-3.2.2-1.5-2.8-2.6-1.8.7-3.1-.7-3.1 2.6-1.8 1.5-2.8 3.2.2 3-1.15Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                                        <path d="m8.25 12.2 2.35 2.35 5.15-5.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                        </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8">
            <div>
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-3">Username</div>
                <div class="font-display text-xl">{{ '@'.($profile['name'] ?? '—') }}</div>
            </div>
            <div>
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-3">User ID</div>
                <div class="font-display text-xl tabular-nums">{{ $userId }}</div>
            </div>
            <div>
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-3">Created</div>
                <div class="font-display text-xl">
                    {{ isset($profile['created']) ? \Carbon\Carbon::parse($profile['created'])->format('d.m.Y') : '—' }}
                </div>
            </div>
            <div>
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-3">Status</div>
                <div class="font-display text-xl {{ ($profile['isBanned'] ?? false) ? 'text-signal' : 'text-acid' }}">
                    {{ ($profile['isBanned'] ?? false) ? 'Banned' : 'Active' }}
                </div>
            </div>
        </div>

        @if(!empty($profile['description']))
            <div class="pt-8 border-t border-white/8">
                <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-4">Description</div>
                <p class="text-paper/80 leading-relaxed whitespace-pre-line font-serif text-xl italic">
                    {{ $profile['description'] }}
                </p>
            </div>
        @endif
    </div>
</div>

@endsection