@extends('layouts.app')
@section('title', 'Inventory')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="03" title="Inventory"
              :meta="'Public Roblox items'">
    @include('partials.export-menu', ['userId' => $userId, 'category' => $assetTypeId])
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

<div class="inventory-toolbar">
    <p class="inventory-result">
        Showing <strong>{{ count($items) }}</strong> of <strong>{{ $total ?? '—' }}</strong> items
    </p>
    <form method="GET" action="{{ route('dashboard.inventory', $userId) }}" class="inventory-filter">
        <label for="assetType">Category</label>
        <select id="assetType" name="type" onchange="this.form.requestSubmit()">
            @foreach($assetTypes as $typeId => $typeName)
                <option value="{{ $typeId }}" @selected($assetTypeId === $typeId)>{{ $typeName }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($error)
    <div class="mb-8"><x-error-state label="Inventory unavailable" :message="$error" /></div>
@endif

<div id="gridLoading" class="inventory-loading hidden" role="status" aria-live="polite">
    <p>Loading inventory page…</p>
    @include('partials.skeleton-grid')
</div>

@if(empty($items))
    <x-empty-state label="Inventory" message="No items found or inventory is private." />
@else
    <div class="inventory-grid" data-inventory-grid>
        @foreach($items as $i => $item)
            @php $assetId = $item['assetId'] ?? $item['id'] ?? null; @endphp
            <div class="inventory-cell">
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