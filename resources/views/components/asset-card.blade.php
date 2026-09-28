@props(['item', 'thumbnail' => null, 'num' => null])

@php
$assetId = $item['assetId'] ?? null;
$name    = $item['name'] ?? 'Unknown';
$rap     = $item['recentAveragePrice'] ?? null;
$serial  = $item['serialNumber'] ?? null;
$category = $item['assetType'] ?? $item['assetTypeId'] ?? null;
@endphp

<div class="editorial-card asset-card bg-ink border border-white/8 hover:border-acid transition-colors group cursor-pointer relative"
    data-reveal="scale"
    data-name="{{ $name }}"
    data-asset-id="{{ $assetId }}"
    data-image="{{ $thumbnail }}"
    data-category="{{ $category }}"
    data-rap="{{ $rap }}"
    data-serial="{{ $serial }}"
    onclick="openItemModal(this.dataset)">

    @if($num)
        <div class="absolute top-3 left-3 z-10 font-mono text-[9px] tracking-[0.2em]
                    text-paper/60 bg-ink/80 backdrop-blur px-1.5 py-0.5">
            {{ $num }}
        </div>
    @endif

    <div class="asset-card-image aspect-square bg-ink-2 overflow-hidden">
        @if($thumbnail)
            <img src="{{ $thumbnail }}" loading="lazy" alt=""
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
        @else
            <div class="w-full h-full grid place-items-center font-mono text-[10px]
                        text-paper/15 uppercase tracking-[0.2em]">
                No image
            </div>
        @endif
    </div>

    <div class="asset-card-info p-4 border-t border-white/8">
        <div class="font-display text-[13px] uppercase tracking-tight truncate">{{ $name }}</div>
        <div class="asset-card-meta mt-3 flex items-center justify-between font-mono text-[10px] tracking-wider text-paper/40">
            <span>#{{ $assetId ?? '—' }}</span>
            @if($rap !== null)
                <span class="text-acid">{{ number_format($rap) }}R</span>
            @elseif($serial)
                <span>SN {{ $serial }}</span>
            @else
                <span>—</span>
            @endif
        </div>
    </div>
</div>