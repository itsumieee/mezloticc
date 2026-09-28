<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Roblox Account Checker')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}?v={{ filemtime(public_path('css/editorial.css')) }}">
</head>
<body class="bg-ink text-paper font-sans min-h-screen antialiased"
      data-dashboard="{{ request()->routeIs('dashboard.*') ? 'true' : 'false' }}"
      data-user-id="{{ (int) request()->route('userId') }}">
    @include('partials.atmosphere')
    <div class="ambient-glow" aria-hidden="true"></div>
    <div class="cursor-light" aria-hidden="true"></div>

    @include('partials.topnav')
    @if(request()->routeIs('dashboard.*'))
        @include('partials.mobile-nav')
    @endif

    <div class="page-frame flex @hasSection('sidebar') has-dashboard @endif">
        @hasSection('sidebar')
            <aside class="sidebar-panel hidden md:block w-[260px] shrink-0 border-r border-white/8 min-h-[calc(100vh-56px)] p-6 sticky top-14 h-[calc(100vh-56px)] overflow-y-auto">
                @yield('sidebar')
            </aside>
        @endif

        <main class="page-main flex-1 min-w-0 p-6 md:p-10 lg:p-14">
            @yield('content')
        </main>
    </div>

    @include('partials.command-palette')
    <script src="{{ asset('js/editorial.js') }}?v={{ filemtime(public_path('js/editorial.js')) }}" defer></script>
    <script src="{{ asset('js/command-palette.js') }}?v={{ filemtime(public_path('js/command-palette.js')) }}" defer></script>
</body>
</html>