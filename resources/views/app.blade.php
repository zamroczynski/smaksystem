<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark'=> ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @routes
    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    {{-- Loader HTML/CSS --}}
    <div id="initial-loading-spinner" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.8); /* Jasne tło z przezroczystością */
            z-index: 9999;
            transition: opacity 0.3s ease-out; /* Płynne znikanie */
            opacity: 1;
        ">
        <svg class="animate-spin h-12 w-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    @inertia
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appRoot = document.getElementById('app');
            if (appRoot) {
                const observer = new MutationObserver((mutationsList, observer) => {
                    if (appRoot.children.length > 0) {
                        const spinner = document.getElementById('initial-loading-spinner');
                        if (spinner) {
                            spinner.style.opacity = '0';
                            setTimeout(() => spinner.remove(), 300);
                        }
                        observer.disconnect(); 
                    }
                });

                observer.observe(appRoot, {
                    childList: true
                });

                if (appRoot.children.length > 0) {
                    const spinner = document.getElementById('initial-loading-spinner');
                    if (spinner) {
                        spinner.style.opacity = '0';
                        setTimeout(() => spinner.remove(), 300);
                    }
                }
            }
        });
    </script>
</body>

</html>