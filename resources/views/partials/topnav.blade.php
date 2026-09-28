<header class="site-nav h-14 border-b border-white/8 flex items-center justify-between px-6 sticky top-0 bg-ink/90 backdrop-blur-lg z-40" data-reveal>

    @if(request()->routeIs('dashboard.*'))
        <button type="button" id="mobileNavTrigger" class="mobile-nav-trigger" onclick="openMobileNav()"
            aria-label="Open dashboard menu" aria-controls="mobileNav" aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
    @endif

    <a href="{{ route('home') }}" class="site-brand flex items-center gap-3 group">
        <div class="brand-mark grid place-items-center group-hover:border-acid transition-colors" aria-hidden="true"></div>
        <span class="font-mono text-[10px] tracking-[0.25em] uppercase text-paper/80">
            Roblox<span class="text-paper/30 mx-2">/</span>Account<span class="text-paper/30 mx-2">/</span>Checker
        </span>
    </a>

    <div class="nav-context hidden md:flex items-center gap-2">
        <span class="live-dot"></span>
        <span>Public Roblox data</span>
    </div>

    <div class="nav-actions">
        <button type="button" class="nav-command" onclick="openCmdPalette()" aria-keyshortcuts="Control+K Meta+K">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
            <span>Search</span><kbd>Ctrl K</kbd>
        </button>
        <a href="{{ route('home') }}" class="nav-action">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
            <span>New check</span>
        </a>
    </div>

</header>