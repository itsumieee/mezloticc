@extends('layouts.app')
@section('title', 'Roblox Account Checker')

@section('content')
<div class="home-page">
    <div class="home-topline" data-reveal="left">
        <span>Roblox Account Checker</span>
        <span class="line"></span>
        <span>Public profile lookup</span>
    </div>

    <section class="home-hero-grid">
        <div data-reveal>
            <div class="home-kicker">
                <span class="live-dot"></span>
                <span>Public Roblox data</span>
            </div>

            <h1 class="home-title">
                Find a Roblox profile
            </h1>

            <p class="home-description">
                Look up a username to explore its public profile, avatar, inventory and collectibles.
            </p>
        </div>

        <aside class="home-aside" data-reveal>
            <div class="home-aside-index">Privacy first</div>
            <p class="home-aside-copy">No password, cookie or account access needed. Only public Roblox data is checked.</p>
            <div class="home-aside-stats">
                <div><strong>01</strong>Username needed</div>
                <div><strong>0</strong>Credentials stored</div>
            </div>
        </aside>
    </section>

    @if(session('error'))
        <div class="mt-8 reveal"><x-error-state label="Lookup error" :message="session('error')" /></div>
    @endif
    @if($errors->any())
        <div class="mt-8 reveal"><x-error-state label="Check input" :message="$errors->first()" /></div>
    @endif

    <form method="POST" action="{{ route('search') }}" id="searchForm" class="search-panel" data-reveal>
        @csrf
        <label for="usernameInput">Roblox username or ID</label>
        <div class="search-control">
            <span class="search-prefix" aria-hidden="true">@</span>
            <input class="search-input" type="text" name="username" id="usernameInput"
                     value="{{ old('username', request('q')) }}" placeholder="builderman or 156" required autofocus autocomplete="off">
            <button class="search-submit" type="submit" id="searchBtn"><span id="btnText">Check profile</span></button>
        </div>
        <div class="search-meta">
            <span>Enter a username or numeric user ID</span>
            <span>Press Enter · Ctrl K</span>
        </div>
    </form>
    <div id="loadingState" class="lookup-indicator hidden" role="status" aria-live="polite">Querying public Roblox data…</div>

    @include('partials.ticker')

    <footer class="home-footer" data-reveal>
        <div class="home-footer-meta">
            <span>Public API</span><span>Cached · rate limited</span><span>Privacy first</span>
        </div>
        <nav class="home-footer-nav" aria-label="More tools">
            <a href="{{ route('compare.form') }}" class="editorial-link">Compare</a>
            <a href="{{ route('watchlist.index') }}" class="editorial-link">Watchlist</a>
            <a href="{{ route('history') }}" class="editorial-link editorial-link-history">Recent searches</a>
            <a href="{{ route('api.docs') }}" class="editorial-link">API docs</a>
        </nav>
    </footer>
</div>

<script>
document.getElementById('searchForm')?.addEventListener('submit', () => {
    document.getElementById('btnText').textContent = '···';
    document.getElementById('searchBtn').disabled = true;
    document.getElementById('loadingState').classList.remove('hidden');
});
</script>
@endsection