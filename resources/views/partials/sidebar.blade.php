@php
$uid = $userId ?? request()->route('userId');
$items = [
    ['dashboard.overview',   '01', 'Overview'],
    ['dashboard.profile',    '02', 'Profile'],
    ['dashboard.inventory',  '03', 'Inventory'],
    ['dashboard.limited',    '04', 'Limited'],
    ['dashboard.animations', '05', 'Animations'],
    ['dashboard.avatar',     '06', 'Avatar'],
    ['dashboard.bundles',    '07', 'Bundles'],
    ['dashboard.statistics', '08', 'Statistics'],
    ['dashboard.value',      '09', 'Value'],
];
@endphp

<div class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/40 mb-5">
    Index
</div>

<nav class="border-t border-white/8">
    @foreach($items as [$route, $num, $label])
        @php $active = request()->routeIs($route); @endphp
        <a href="{{ route($route, $uid) }}"
           class="flex items-center justify-between py-3 border-b border-white/8 group transition-colors
                  {{ $active ? 'text-acid' : 'text-paper/60 hover:text-paper' }}">
            <span class="flex items-center gap-3">
                <span class="font-mono text-[10px] {{ $active ? 'text-acid' : 'text-paper/25' }}">{{ $num }}</span>
                <span class="font-display text-[13px] uppercase tracking-wide">{{ $label }}</span>
            </span>
            <span class="font-mono text-xs transition-transform group-hover:translate-x-1
                         {{ $active ? 'translate-x-0 opacity-100' : '-translate-x-2 opacity-0 group-hover:opacity-100' }}">→</span>
        </a>
    @endforeach
</nav>

<div class="mt-10 space-y-3 font-mono text-[10px] tracking-[0.2em] uppercase">
    <div class="flex justify-between">
        <span class="text-paper/30">User ID</span>
        <span>{{ $uid }}</span>
    </div>
    <div class="flex justify-between">
        <span class="text-paper/30">Mode</span>
        <span class="text-acid">Public</span>
    </div>
    <div class="flex justify-between">
        <span class="text-paper/30">Cache</span>
        <span>Active</span>
    </div>
</div>
