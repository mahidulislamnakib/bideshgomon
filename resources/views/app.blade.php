<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="theme-color" content="#64ac44">
        <meta name="format-detection" content="telephone=no">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts - Plus Jakarta Sans (Modern geometric sans-serif) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts and Styles (CSS imported via app.js) -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
        
        <!-- Unregister any previously registered service workers -->
        <script src="/unregister-sw.js"></script>
        
        <!-- Cache Buster -->
        <meta name="asset-version" content="{{ config('app.asset_version', time()) }}">
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
