<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="theme-color" content="#4F46E5">
        <meta name="format-detection" content="telephone=no">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=proxima-nova:400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Tailwind CSS CDN -->
        <!-- NOTE: CSS imports disabled in app.js due to Sucrase parser bug -->
        <!-- This CDN provides base Tailwind functionality until parser issue resolved -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'brand-red': {
                                600: '#e4282b',
                                700: '#c02326'
                            }
                        }
                    }
                }
            }
        </script>

        <!-- Scripts -->
        @routes
        @if(app()->environment('testing'))
            @vite(['resources/js/app.js'])
        @else
            @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @endif
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
