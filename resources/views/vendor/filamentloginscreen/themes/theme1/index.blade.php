@props([
    'heading' => null,
    'subheading' => null,
])

<div class="flex h-screen">
    <!-- Left Pane -->
    <div class="hidden lg:block flex-1 bg-white text-black relative">
        <!-- Imagen PNG de restaurante -->
        <img src="{{ asset('images/restaurante.png') }}" alt="Restaurante" class="w-full h-full object-cover">
        <h2 class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-2xl font-bold text-gray-800 bg-white/80 px-4 py-2 rounded"></h2>
    </div>
    <!-- Right Pane -->
    <div class=" bg-gray-100 lg:w-1/2 flex items-center justify-center">
        <div class="max-w-md w-full p-6">
            <section class="grid auto-cols-fr gap-y-6">
                <x-filament-panels::header.simple
                        :heading="$heading ??= $this->getHeading()"
                        :logo="$this->hasLogo()"
                        :subheading="$subheading ??= $this->getSubHeading()"
                />
                @if (filament()->hasRegistration())
                    <x-slot name="subheading">
                        {{ __('filament-panels::pages/auth/login.actions.register.before') }}

                        {{ $this->registerAction }}
                    </x-slot>
                @endif


                <x-filament-panels::form wire:submit="authenticate">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                            :actions="$this->getCachedFormActions()"
                            :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>


            </section>

    </div>
</div>
