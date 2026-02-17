<x-filament::page>
    <!-- Header con título y estado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">
                Detalle de Caja #{{ $record->id }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ $record->opening_datetime ? $record->opening_datetime->format('d/m/Y H:i') : 'No disponible' }} -
                {{ $record->closing_datetime ? $record->closing_datetime->format('d/m/Y H:i') : 'En curso' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div
                class="px-3 py-1 text-sm rounded-full font-medium {{ $record->closing_datetime ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $record->closing_datetime ? 'Cerrada' : 'Abierta' }}
            </div>
            @if($record->openedBy)
                <div class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800 font-medium">
                    {{ $record->openedBy->name }}
                </div>
            @endif
        </div>
    </div>

    @php
        $initialAmount = (float) $record->opening_amount;
        $issuedInvoices = $record->invoices->filter(function ($invoice) {
            return is_null($invoice->voided_date)
                && strtolower((string) $invoice->tax_authority_status) !== 'voided';
        });
        $totalIngresos = (float) $issuedInvoices->sum('total');
        $totalEgresos = (float) ($record->cashRegisterExpenses->sum('amount') + $record->cashMovements->where('movement_type', 'egreso')->sum('amount'));
        $saldoTeorico = ($initialAmount + $totalIngresos) - $totalEgresos;
    @endphp

    <!-- Tarjetas de resumen -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Monto Inicial</p>
                <p class="text-2xl font-bold text-gray-900">S/ {{ number_format($initialAmount, 2) }}</p>
            </div>
        </x-filament::card>

        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Ingresos Totales</p>
                <p class="text-2xl font-bold text-green-600">
                    S/ {{ number_format($totalIngresos, 2) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Comprobantes emitidos no anulados</p>
            </div>
        </x-filament::card>

        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Egresos Totales</p>
                <p class="text-2xl font-bold text-red-600">
                    S/ {{ number_format($totalEgresos, 2) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Gastos y salidas</p>
            </div>
        </x-filament::card>

        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Saldo Final Teórico</p>
                <p class="text-2xl font-bold text-blue-600">S/ {{ number_format($saldoTeorico, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Inicial + Ingresos - Egresos</p>
            </div>
        </x-filament::card>
    </div>

    <!-- Footer con acciones -->
    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
        <x-filament::button tag="a" href="/admin/cash-register-reports/{{ $record->id }}" color="gray"
            icon="heroicon-o-arrow-right" class="cursor-pointer">
            Ver reporte completo
        </x-filament::button>
    </div>
</x-filament::page>
