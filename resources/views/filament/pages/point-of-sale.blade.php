<x-filament::page>
    <!-- Esta página redirigirá automáticamente al sistema POS -->
    <div class="flex items-center justify-center p-6">
        <div class="text-center">
            <div class="text-xl font-medium">Redirigiendo al Punto de Venta...</div>
            <div class="mt-2">Si no eres redirigido automáticamente, <a href="{{ route('pos.index') }}" class="text-primary-600 hover:text-primary-500">haz clic aquí</a>.</div>
        </div>
    </div>
</x-filament::page>
