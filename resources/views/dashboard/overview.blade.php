@extends('layouts.app')
@section('title', 'Overview')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="01" title="Overview"
              :meta="'User · ' . ($profile['name'] ?? 'unknown') . ' · ' . $userId">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

{{-- Profile strip --}}
<div class="profile-feature" data-reveal="scale">
    <div class="profile-feature-visual">
        <span class="profile-photo-index">01 / AVATAR</span>
        @if($headshot)
            <img src="{{ $headshot }}" alt=""
                 class="profile-feature-image" data-scroll-float>
        @endif
        <span class="profile-photo-caption">PUBLIC PROFILE<br>{{ $userId }}</span>
    </div>

    <div class="profile-feature-content">
        <div>
            <div class="profile-state">
                <span class="live-dot"></span>
                {{ ($profile['isBanned'] ?? false) ? 'Banned account' : 'Public account' }}
            </div>
            <h2 class="profile-display-name">
                <span>{{ $profile['displayName'] ?? 'Unknown' }}</span>
                @if(!empty($profile['hasVerifiedBadge']))
                                        <span role="img" aria-label="Verified Roblox account" title="Verified Roblox account"
                                              class="roblox-verified-badge">
                                                <svg viewBox="0 0 24 24" class="size-full" fill="none" aria-hidden="true">
                                                        <path d="M12 2.25 15 3.4l3.2-.2 1.5 2.8 2.6 1.8-.7 3.1.7 3.1-2.6 1.8-1.5 2.8-3.2-.2-3 1.15-3-1.15-3.2.2-1.5-2.8-2.6-1.8.7-3.1-.7-3.1 2.6-1.8 1.5-2.8 3.2.2 3-1.15Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                                        <path d="m8.25 12.2 2.35 2.35 5.15-5.2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                        </span>
                @endif
            </h2>
            <p class="profile-username">
                {{ '@'.($profile['name'] ?? 'unknown') }}
            </p>
        </div>

        <div class="profile-metadata">
            <div class="profile-metadata-item">
                <div class="profile-meta-label">User ID</div>
                <div class="profile-meta-value">{{ $userId }}</div>
            </div>
            <div class="profile-metadata-item">
                <div class="profile-meta-label">Joined Roblox</div>
                <div class="profile-meta-value">
                    {{ isset($profile['created']) ? \Carbon\Carbon::parse($profile['created'])->format('d.m.Y') : '—' }}
                </div>
            </div>
            <div class="profile-metadata-item">
                <div class="profile-meta-label">Inventory</div>
                <div class="profile-meta-value {{ $visible ? 'is-public' : 'is-private' }}">
                    {{ $visible ? 'Public' : 'Private' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Metrics --}}
<div class="editorial-section-heading" data-reveal="left">
    <span class="section-index">02</span>
    <h2>Account at a glance</h2>
    <span class="section-rule"></span>
    <span class="section-note">PUBLIC DATA</span>
</div>

<div class="overview-metrics" data-reveal="scale">
    @foreach([
        ['01', 'Limited',    $stats['limited'] ?? 0,                false],
        ['02', 'Wearing',    $stats['wearing'] ?? 0,                false],
        ['03', 'Animations', $stats['animations'] ?? '—',           false],
        ['04', 'Robux',      'N/A',                                 true ],
    ] as [$number, $label, $value, $dim])
        <div class="overview-metric">
            <div class="overview-metric-topline">
                <div class="overview-metric-label">{{ $label }}</div>
                <span>{{ $number }}</span>
            </div>
            <div class="overview-metric-value {{ $dim ? 'is-muted' : '' }}">
                {{ $value }}
            </div>
            <span class="metric-mark" aria-hidden="true"></span>
        </div>
    @endforeach
</div>

@if(!empty($statsErrors))
    <p class="mt-5 font-mono text-[9px] tracking-wider text-signal">
        Could not count every page for: {{ implode(', ', array_keys($statsErrors)) }}.
    </p>
@endif

@if(!$visible)
    <div class="mt-10 border-l-2 border-signal pl-4 py-2">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-1">Notice</div>
        <p class="text-sm text-paper/70">Inventory is private. Some data cannot be displayed.</p>
    </div>
@endif

@endsection