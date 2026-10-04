@props(['num' => '00', 'title' => '', 'meta' => null])

<div class="page-header mb-12" data-reveal>
    <div class="flex items-center gap-4 font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-8">
        <span>{{ $num }}</span>
        <span class="h-px flex-1 bg-white/8"></span>
        <span>{{ now()->format('d.m.Y') }}</span>
    </div>

    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-4xl md:text-6xl uppercase tracking-tighter leading-[0.95]">
                {{ $title }}
            </h1>
            @if($meta)
                <p class="mt-4 font-mono text-[11px] tracking-[0.2em] uppercase text-paper/50">
                    {{ $meta }}
                </p>
            @endif
        </div>

        @if(!$slot->isEmpty())
            <div class="flex flex-wrap gap-3">
                @if(request()->routeIs('dashboard.*'))
                    @include('partials.share-button')
                @endif
                {{ $slot }}
            </div>
        @endif
    </div>
</div>