@props(['userId', 'category' => 'collectibles'])

<details class="export-menu">
    <summary class="border border-white/15 hover:border-acid hover:text-acid px-4 py-2 font-mono text-[10px] tracking-[0.25em] uppercase transition-colors">
        Export
    </summary>
    <div class="export-menu-options">
        <a href="{{ route('dashboard.export', ['userId' => $userId, 'format' => 'csv', 'category' => $category]) }}">Download CSV</a>
        <a href="{{ route('dashboard.export', ['userId' => $userId, 'format' => 'json', 'category' => $category]) }}">Download JSON</a>
    </div>
</details>