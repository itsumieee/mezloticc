@props(['sortOptions' => [], 'currentSort' => 'rap_desc', 'searchable' => true])

<div class="flex flex-wrap items-stretch gap-px bg-white/8 border border-white/8 mb-8">
    @if($searchable)
        <div class="flex-1 min-w-[220px] bg-ink flex items-center gap-3 px-5 py-4">
            <span class="font-mono text-paper/30">⌕</span>
            <input type="text" id="itemSearch"
                   placeholder="Filter items..."
                   class="w-full bg-transparent font-mono text-xs tracking-wider focus:outline-none placeholder:text-paper/25 text-paper">
        </div>
    @endif

    <div class="bg-ink flex items-center px-5 py-4 border-l border-white/8">
        <span class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mr-4">Sort</span>
        <select id="sortSelect"
                class="bg-transparent font-mono text-xs tracking-wider text-paper focus:outline-none focus:text-acid cursor-pointer appearance-none pr-6">
            @foreach($sortOptions as $value => $label)
                <option value="{{ $value }}" @selected($currentSort === $value) class="bg-ink text-paper">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-ink flex items-center px-5 py-4 border-l border-white/8">
        <span class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mr-3">Count</span>
        <span id="visibleCount" class="font-mono text-xs text-acid tabular-nums">—</span>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById('itemSearch');
    const sort = document.getElementById('sortSelect');
    const grid = document.getElementById('assetGrid');
    const counter = document.getElementById('visibleCount');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('[data-item]'));

    function applyFilter() {
        const q = (input?.value || '').trim().toLowerCase();
        let visible = 0;

        cards.forEach((card) => {
            const name = card.dataset.name?.toLowerCase() || '';
            const id = card.dataset.assetId || '';
            const match = !q || name.includes(q) || id.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (counter) counter.textContent = visible;
    }

    function applySort() {
        const mode = sort?.value || 'default';
        const sorted = [...cards].sort((a, b) => {
            const ra = parseInt(a.dataset.rap || '-1', 10);
            const rb = parseInt(b.dataset.rap || '-1', 10);
            const na = (a.dataset.name || '').toLowerCase();
            const nb = (b.dataset.name || '').toLowerCase();

            switch (mode) {
                case 'rap_desc': return rb - ra;
                case 'rap_asc': return (ra < 0 ? Infinity : ra) - (rb < 0 ? Infinity : rb);
                case 'name_asc': return na.localeCompare(nb);
                case 'name_desc': return nb.localeCompare(na);
                case 'id_asc': return (+a.dataset.assetId || 0) - (+b.dataset.assetId || 0);
                case 'id_desc': return (+b.dataset.assetId || 0) - (+a.dataset.assetId || 0);
                default: return 0;
            }
        });

        sorted.forEach((card) => grid.appendChild(card));
    }

    input?.addEventListener('input', applyFilter);
    sort?.addEventListener('change', () => {
        applySort();
        applyFilter();
    });

    applyFilter();
})();
</script>
