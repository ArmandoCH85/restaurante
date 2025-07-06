<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
    ])

    <style>
    /* FORZAR COLOR NEGRO EN TODOS LOS NAVEGADORES - SOLUCIÓN KISS */
    * {
        color: #000 !important;
    }
    input, label, button, span, div, h1, h2, h3, h4, h5, h6, p, a {
        color: #000 !important;
    }
    </style>

    <div class="fi-simple-layout flex min-h-screen flex-col items-center">
        @if (($hasTopbar ?? true) && filament()->auth()->check())
            <div
                    class="absolute end-0 top-0 flex h-16 items-center gap-x-4 pe-4 md:pe-6 lg:pe-8"
            >
                @if (filament()->hasDatabaseNotifications())
                    @livewire(Filament\Livewire\DatabaseNotifications::class, ['lazy' => true])
                @endif

                <x-filament-panels::user-menu />
            </div>
        @endif

        <div
                class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center"
        >
            <main
                    @class([
                        'fi-simple-main  w-full bg-white'
                    ])
            >
                {{ $slot }}
            </main>
        </div>

    </div>
</x-filament-panels::layout.base>
