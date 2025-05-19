<x-filament-panels::page>
    <div class="flex justify-center">
        <div class="text-center">
            <h2 class="text-xl font-bold">Generando PDF...</h2>
            <p class="mt-2">El PDF se está generando y se abrirá en una nueva ventana.</p>
            <div class="mt-4">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.quotations.index') }}"
                    color="gray"
                >
                    Volver al listado
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
