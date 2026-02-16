<x-filament::page>
    <!-- Header con título y estado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">
                Reporte de Caja #{{ $record->id }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ $record->opening_datetime ? $record->opening_datetime->format('d/m/Y H:i') : 'No disponible' }} -
                {{ $record->closing_datetime ? $record->closing_datetime->format('d/m/Y H:i') : 'En curso' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <div class="px-3 py-1 text-sm rounded-full font-medium {{ $record->closing_datetime ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $record->closing_datetime ? 'Cerrada' : 'Abierta' }}
            </div>
            @if($record->openedBy)
            <div class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800 font-medium">
                {{ $record->openedBy->name }}
            </div>
            @endif
        </div>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Monto Inicial</p>
                <p class="text-2xl font-bold text-gray-900">S/ {{ number_format($record->opening_amount, 2) }}</p>
            </div>
        </x-filament::card>

        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Monto Final</p>
                <p class="text-2xl font-bold text-gray-900">S/ {{ number_format($record->actual_amount, 2) }}</p>
            </div>
        </x-filament::card>

        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Total Movimientos</p>
                <p class="text-2xl font-bold text-gray-900">{{ $record->cashMovements->count() }}</p>
            </div>
        </x-filament::card>

        <x-filament::card class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <div class="p-4">
                <p class="text-sm font-medium text-gray-500">Total Ventas</p>
                <p class="text-2xl font-bold text-gray-900">S/ {{ number_format($record->orders->sum('total'), 2) }}</p>
            </div>
        </x-filament::card>
    </div>

    <!-- Desglose por métodos de pago -->
    <div class="mb-6">
        <h3 class="text-lg font-medium mb-2">Desglose por Métodos de Pago</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $paymentMethods = [
                    'cash' => 'Efectivo',
                    'credit_card' => 'Tarjeta Crédito',
                    'debit_card' => 'Tarjeta Débito',
                    'bank_transfer' => 'Transferencia',
                    'digital_wallet' => 'Billetera Digital'
                ];

                $paymentTotals = [];
                foreach($record->payments as $payment) {
                    $paymentTotals[$payment->payment_method] = ($paymentTotals[$payment->payment_method] ?? 0) + $payment->amount;
                }

                $totalOrders = $record->orders->sum('total');
            @endphp

            @foreach($paymentMethods as $method => $label)
                @if(isset($paymentTotals[$method]))
                    <x-filament::card class="hover:shadow-lg transition-shadow duration-200">
                        <div class="p-4">
                            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                            <p class="text-xl font-bold">S/ {{ number_format($paymentTotals[$method], 2) }}</p>
                            @if ($totalOrders > 0)
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ number_format(($paymentTotals[$method] / $totalOrders) * 100, 1) }}% del total
                                </p>
                            @endif
                        </div>
                    </x-filament::card>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Pestañas para mejor organización -->
    <div x-data="{ activeTab: 'movements' }" class="space-y-6">
        <!-- Navegación de pestañas -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                    @click="activeTab = 'movements'"
                    :class="{
                        'border-primary-500 text-primary-600': activeTab === 'movements',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'movements',
                    }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 outline-none"
                >
                    Movimientos de Caja
                </button>
                <button
                    @click="activeTab = 'sales'"
                    :class="{
                        'border-primary-500 text-primary-600': activeTab === 'sales',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'sales',
                    }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 outline-none"
                >
                    Ventas ({{ count($record->orders) }})
                </button>
            </nav>
        </div>

        <!-- Contenido de pestaña de Movimientos -->
        <div x-show="activeTab === 'movements'">
            <x-filament::card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha/Hora
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Motivo
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($record->cashMovements as $movement)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $movement->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $movement->movement_type === 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $movement->movement_type ?? 'No especificado' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $movement->movement_type === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->movement_type === 'ingreso' ? '+' : '-' }} S/ {{ number_format($movement->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ $movement->reason ?? 'Sin motivo' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $movement->approvedByUser?->name ?? 'No asignado' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No hay movimientos registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        </div>

        <!-- Contenido de pestaña de Ventas -->
        <div x-show="activeTab === 'sales'">
            <x-filament::card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha/Hora
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    N° Orden
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Método Pago
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($record->orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->order_datetime?->format('d/m/Y H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        S/ {{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($order->payments->isNotEmpty())
                                            @foreach($order->payments as $payment)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                    {{ $payment->payment_method }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-500">No especificado</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->user?->name ?? 'No asignado' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No hay ventas registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        </div>
    </div>

    <!-- Footer con acciones -->
    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
        @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']))
        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.export-cash-register-pdf', ['id' => $record->id]) }}"
            color="gray"
            icon="heroicon-o-arrow-down-tray"
            target="_blank"
            class="cursor-pointer"
        >
            Exportar PDF
        </x-filament::button>
        @endif
    </div>
</x-filament::page>