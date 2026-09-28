@php
$mobileUserId = (int) request()->route('userId');
$mobileItems = [
    ['dashboard.overview', '01', 'Overview'],
    ['dashboard.profile', '02', 'Profile'],
    ['dashboard.inventory', '03', 'Inventory'],
    ['dashboard.limited', '04', 'Limited'],
    ['dashboard.animations', '05', 'Animations'],
    ['dashboard.avatar', '06', 'Avatar'],
    ['dashboard.bundles', '07', 'Bundles'],
    ['dashboard.statistics', '08', 'Statistics'],
    ['dashboard.value', '09', 'Value'],
];
@endphp

<div id="mobileNav" class="mobile-nav-backdrop" aria-hidden="true" onclick="if(event.target===this) closeMobileNav()">
    <nav class="mobile-nav-drawer" aria-label="Dashboard navigation">
        <div class="mobile-nav-heading">
            <span>Index</span>
            <button type="button" onclick="closeMobileNav()" aria-label="Close navigation">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <div class="mobile-nav-links">
            @foreach($mobileItems as [$route, $number, $label])
                @php $active = request()->routeIs($route); @endphp
                <a href="{{ route($route, $mobileUserId) }}" @if($active) aria-current="page" @endif>
                    <span>{{ $number }}</span><span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
        <div class="mobile-nav-account">User ID <strong>{{ $mobileUserId }}</strong></div>
    </nav>
</div>
