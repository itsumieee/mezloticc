<meta name="description" content="Inspect public Roblox account data — profile, inventory, limited items, animations, bundles. No credentials requested.">
<meta name="theme-color" content="#090909">
<meta name="color-scheme" content="dark">

<meta property="og:type" content="website">
<meta property="og:title" content="@yield('title', 'Roblox Account Checker')">
<meta property="og:description" content="Public Roblox account inspector. No credentials requested.">
<meta property="og:site_name" content="Roblox Account Checker">

@if(request()->routeIs('dashboard.*') && request()->route('userId'))
      @php($ogImageUrl = route('og.image', ['userId' => request()->route('userId')]))
      <meta property="og:image" content="{{ $ogImageUrl }}">
      <meta property="og:image:type" content="image/svg+xml">
      <meta property="og:image:width" content="1200">
      <meta property="og:image:height" content="630">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="@yield('title', 'Roblox Account Checker')">
<meta name="twitter:description" content="Public Roblox account inspector. No credentials requested.">

<link rel="icon" type="image/svg+xml"
      href="data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><rect width=%2232%22 height=%2232%22 fill=%22%23090909%22/><rect x=%2210%22 y=%2210%22 width=%2212%22 height=%2212%22 fill=%22%23e22630%22/></svg>">
