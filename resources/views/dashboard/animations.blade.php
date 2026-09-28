@extends('layouts.app')
@section('title', 'Animations')
@section('sidebar') @include('partials.sidebar', ['userId' => $userId]) @endsection

@section('content')

<x-page-header num="05" title="Animations"
              :meta="'Emotes · ' . count($items) . ' / ' . ($total ?? '—')">
    @include('partials.refresh-button', ['userId' => $userId])
</x-page-header>

@if($error)
    <div class="border-l-2 border-signal pl-4 py-2 mb-8">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-signal mb-1">Notice</div>
        <p class="text-sm text-paper/70">{{ $error }}</p>
    </div>
@endif

@if(empty($items))
    <div class="border border-white/8 p-16 text-center">
        <div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-4">Empty</div>
        <p class="font-serif italic text-2xl text-paper/60">Data unavailable.</p>
    </div>
@else
    <div class="border border-white/8 divide-y divide-white/8" data-reveal="scale">
        @foreach($items as $i => $item)
            @php $assetId = $item['assetId'] ?? $item['id'] ?? null; @endphp
            <a href="https://www.roblox.com/catalog/{{ $assetId }}" target="_blank"
               class="flex items-center justify-between p-6 hover:bg-ink-2 transition-colors group">
                <div class="flex items-center gap-6">
                    <span class="font-mono text-[10px] text-paper/30 tabular-nums">
                        {{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        <div class="font-display text-lg uppercase tracking-tight group-hover:text-acid transition-colors">
                            {{ $item['name'] ?? 'Unknown' }}
                        </div>
                        <div class="font-mono text-[10px] text-paper/40 mt-1 tracking-wider">
                            #{{ $assetId }}
                        </div>
                    </div>
                </div>
                <span class="font-mono text-xs text-paper/40 group-hover:text-acid group-hover:translate-x-1 transition-all">→</span>
            </a>
        @endforeach
    </div>
@endif

@endsection