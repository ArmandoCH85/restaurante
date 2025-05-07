@props([
    'footer' => null,
    'header' => null,
    'heading' => null,
    'subheading' => null,
])

<div {{ $attributes->class(['fi-page']) }}>
    @if ($header)
        {{ $header }}
    @elseif ($heading || $subheading)
        <header class="fi-page-header mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <x-filament::header.heading>
                    {{ $heading }}
                </x-filament::header.heading>

                @if ($subheading)
                    <x-filament::header.subheading class="mt-1">
                        {{ $subheading }}
                    </x-filament::header.subheading>
                @endif
            </div>

            @if ($actions = $slot->actions)
                <div class="flex shrink-0 items-center gap-4">
                    {{ $actions }}
                </div>
            @endif
        </header>
    @endif

    {{ $slot }}

    @if ($footer)
        {{ $footer }}
    @endif
</div>

<script src="{{ asset('js/cash-register-modal.js') }}"></script>
