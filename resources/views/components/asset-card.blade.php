@props(['item', 'thumbnail' => null, 'num' => null])

@php
$assetId = $item['assetId'] ?? null;
$name    = $item['name'] ?? 'Unknown';
$rap     = $item['recentAveragePrice'] ?? null;
$serial  = $item['serialNumber'] ?? null;
$category = $item['assetType'] ?? $item['assetTypeId'] ?? null;
@endphp

<div data-item
     data-name="{{ $name }}"
     data-asset-id="{{ $assetId }}"
     data-image="{{ $thumbnail }}"
     data-category="{{ $category }}"
     data-rap="{{ $rap }}"
     data-serial="{{ $serial }}"
     class="group bg-ink hover:bg-ink-2 transition-colors cursor-pointer relative overflow-hidden"
     onclick="openItemModal(this.dataset)">

    @if($num)
        <div class="absolute top-3 left-3 z-20 font-mono text-[9px] tracking-[0.2em] text-paper/60 bg-ink/70 backdrop-blur px-1.5 py-0.5">
            {{ $num }}
        </div>
    @endif

    @if($rap !== null)
        <div class="absolute top-3 right-3 z-20 font-mono text-[9px] tracking-[0.15em] text-ink bg-acid px-1.5 py-0.5">
            {{ number_format($rap) }}R
        </div>
    @endif

    <div class="aspect-square bg-ink-2 overflow-hidden relative">
        @if($thumbnail)
            <img src="{{ $thumbnail }}" loading="lazy" alt=""
                 class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110">
            <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-ink via-ink/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute inset-0 grid place-items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="font-mono text-[9px] tracking-[0.3em] uppercase text-acid border border-acid/60 px-3 py-1.5 bg-ink/60 backdrop-blur">
                    View →
                </div>
            </div>
        @else
            <div class="w-full h-full grid place-items-center font-mono text-[10px] text-paper/15 uppercase tracking-[0.2em]">
                No image
            </div>
        @endif
    </div>

    <div class="p-4 border-t border-white/8 flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="font-display text-[13px] uppercase tracking-tight truncate group-hover:text-acid transition-colors">
                {{ $name }}
            </div>
            <div class="mt-2 font-mono text-[10px] tracking-wider text-paper/40">
                #{{ $assetId ?? '—' }}
            </div>
        </div>
        @if($serial)
            <div class="font-mono text-[9px] tracking-[0.15em] text-paper/30 shrink-0 mt-1">
                SN {{ $serial }}
            </div>
        @endif
    </div>

    <div class="absolute bottom-0 left-0 right-0 h-px bg-white/8 overflow-hidden">
        <div class="h-full w-0 bg-acid group-hover:w-full transition-all duration-500"></div>
    </div>
</div>