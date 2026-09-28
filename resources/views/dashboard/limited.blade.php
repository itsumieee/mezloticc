@extends('layouts.app')
@section('title', 'Limited')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="04" title="Limited"
              :meta="'Collectibles · ' . count($items) . ' / ' . ($total ?? '—')">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

@if($error)
    <div class="mb-8"><x-error-state label="Limited items unavailable" :message="$error" /></div>
@endif

@if(empty($items))
    <x-empty-state
        :label="$error ? 'Data unavailable' : 'No collectibles detected'"
        :message="$error ? 'Roblox could not return this inventory.' : 'No public collectible Limited items were returned by Roblox.'" />
    @if(!$error)
            <p class="text-sm text-paper/40 mt-4 max-w-xl mx-auto">
                Regular hats, clothing and accessories are not counted as Limited collectibles.
                You can confirm the public account inventory on Roblox.
            </p>
            <a href="https://www.roblox.com/users/{{ $userId }}/profile" target="_blank" rel="noopener noreferrer"
               class="editorial-link justify-center mt-6">
                Open Roblox profile <span aria-hidden="true">↗</span>
            </a>
    @endif
@else
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-px bg-white/8 border border-white/8" data-reveal="scale">
        @foreach($items as $i => $item)
            @php $assetId = $item['assetId'] ?? null; @endphp
            <div class="bg-ink">
                <x-asset-card :item="$item"
                              :thumbnail="$assetId ? ($thumbnails[$assetId] ?? null) : null"
                              :num="str_pad($i + 1, 3, '0', STR_PAD_LEFT)" />
            </div>
        @endforeach
    </div>
@endif

@include('partials.pagination')
@include('partials.item-modal')

@endsection