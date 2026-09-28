@if(!empty($nextUrl) || !empty($previousUrl))
    <div class="flex items-center justify-between mt-12 pt-6 border-t border-white/8">
        @if(!empty($previousUrl))
            <a href="{{ $previousUrl }}"
               class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/60 hover:text-acid transition">
                ← Prev
            </a>
        @else
            <span class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/20">← Prev</span>
        @endif

        <span class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/30">Page</span>

        @if(!empty($nextUrl))
            <a href="{{ $nextUrl }}"
               class="font-mono text-[10px] tracking-[0.25em] uppercase text-acid hover:text-paper transition">
                Next →
            </a>
        @else
            <span class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/20">Next →</span>
        @endif
    </div>
@endif