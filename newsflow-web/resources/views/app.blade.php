<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Google AdSense loader — site verification + ad serving. Rendered
             from config('adsense.client') (ADSENSE_CLIENT), the same publisher
             ID that powers /ads.txt and every <ins> ad unit. Pro users still
             load this (it verifies the site) but receive no slot IDs, so no ad
             ever renders for them. --}}
        @if (config('adsense.client'))
            <script async
                    src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('adsense.client') }}"
                    crossorigin="anonymous"></script>
        @endif

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- SEO -->
        <meta name="description" content="NewsFlow builds you a personal newspaper. Follow only the topics you care about and get the day's most popular headlines on each — a more customizable Google News.">
        <meta property="og:title" content="NewsFlow — Your own customized news topics, every day">
        <meta property="og:description" content="Build your own newsroom. Follow the topics you care about and get the day's most popular headlines on each, every morning.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="NewsFlow">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="theme-color" content="#2563eb">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=source-serif-4:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
