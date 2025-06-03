@props([
    'heading' => null,
    'subheading' => null,
])

<div class="flex h-screen w-screen overflow-hidden">
    <!-- Left Pane - Imagen -->
    <div class="hidden lg:flex lg:w-7/10 bg-white text-black relative h-full">
        <!-- Imagen JPG de login - Ajustada para ocupar todo el espacio -->
        <img src="{{ asset('images/login.jpg') }}" alt="Restaurante" class="w-full h-full object-cover object-center">
        <h2 class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-2xl font-bold text-gray-800 bg-white/80 px-4 py-2 rounded"></h2>
    </div>
    <!-- formulario de login actualizado-->
    <div class="bg-gray-100 w-full lg:w-3/10 flex items-center justify-center h-full">
        <div class="w-full max-w-2xl px-10 py-8 lg:pr-16">
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
