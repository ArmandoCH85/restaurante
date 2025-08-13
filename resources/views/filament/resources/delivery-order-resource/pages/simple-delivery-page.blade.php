<x-filament-panels::page>
    <div class="max-w-3xl mx-auto w-full">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-6 space-y-6">

            <form wire:submit="createSimpleDelivery" class="space-y-4">
                {{ $this->simpleForm }}

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <x-filament::button type="submit" icon="heroicon-o-check-circle" color="success">
                        Continuar al POS
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ url('/admin/ventas/delivery') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Volver a la vista avanzada</a>
        </div>
    </div>
</x-filament-panels::page>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // KISS: si el autocompletado no dispara, forzamos la acción de copiar cuando cambia el select
        document.addEventListener('change', (e) => {
            const target = e.target;
            if (!target) return;
            // Detecta el select de cliente por name de campo que Filament genera
            if (target.name && target.name.endsWith('[customer_id]')) {
                // Dispara el botón de copiar mediante Livewire (si existe) o actualiza el formulario
                const lw = window.Livewire?.find?.(document.querySelector('[wire\:id]')?.getAttribute('wire:id'));
                if (lw) {
                    // Ejecuta la acción del formulario (si está expuesta) o invoca el hook livewire
                    lw.call('updatedSimpleCustomerId', target.value);
                }
            }
        }, true);
    });
</script>
@endpush
