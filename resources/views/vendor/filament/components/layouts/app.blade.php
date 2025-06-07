@props([
    'livewire',
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ __('filament::layout.direction') ?? 'ltr' }}"
    @class([
        'fi min-h-screen',
        'dark' => filament()->hasDarkModeForced(),
    ])
>
    <head>
        {{ \Filament\Support\Facades\FilamentView::renderHook('head.start') }}

        <meta charset="utf-8" />
        <meta name="application-name" content="{{ filament()->getBrandName() }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @if ($favicon = filament()->getFavicon())
            <link rel="icon" href="{{ $favicon }}" />
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook('styles.start') }}

        <style>
            [x-cloak=''],
            [x-cloak='x-cloak'],
            [x-cloak='1'] {
                display: none !important;
            }

            @media (max-width: 1023px) {
                [x-cloak='-lg'] {
                    display: none !important;
                }
            }

            @media (min-width: 1024px) {
                [x-cloak='lg'] {
                    display: none !important;
                }
            }
        </style>

        @filamentStyles

        <!-- CSS específico para optimizaciones responsive en monitores 16.3" -->
        <link href="{{ asset('css/admin-responsive-16.css') }}" rel="stylesheet">

        <!-- CSS específico para mejoras en operaciones de caja -->
        <link href="{{ asset('css/cash-register-improvements.css') }}" rel="stylesheet">

        {{ \Filament\Support\Facades\FilamentView::renderHook('styles.end') }}

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    const activeSidebarItem = document.querySelector('.fi-sidebar-item.fi-active')

                    if (! activeSidebarItem) {
                        return
                    }

                    const sidebarWrapper = document.querySelector('.fi-sidebar-nav')

                    if (! sidebarWrapper) {
                        return
                    }

                    sidebarWrapper.scrollTo({
                        top: activeSidebarItem.offsetTop - (sidebarWrapper.offsetHeight / 2) + (activeSidebarItem.offsetHeight / 2),
                        behavior: 'smooth',
                    })
                }, 100)
            })
        </script>

        @if (! filament()->hasDarkMode())
            <script>
                localStorage.setItem('theme', 'light')
            </script>
        @elseif (filament()->hasDarkModeForced())
            <script>
                localStorage.setItem('theme', 'dark')
            </script>
        @else
            <script>
                const theme = localStorage.getItem('theme') ?? @js(filament()->getDefaultThemeMode()->value)

                if (
                    theme === 'dark' ||
                    (theme === 'system' &&
                        window.matchMedia('(prefers-color-scheme: dark)')
                            .matches)
                ) {
                    document.documentElement.classList.add('dark')
                }
            </script>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook('head.end') }}
    </head>

    <body
        class="fi-body min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook('body.start') }}

        {{ $slot }}

        @livewire(Filament\Livewire\Notifications::class)

        {{ \Filament\Support\Facades\FilamentView::renderHook('body.end') }}

        @filamentScripts(withCore: true)

        @stack('scripts')


    </body>
</html>
