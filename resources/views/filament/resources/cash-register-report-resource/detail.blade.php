<x-filament::section>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Información General -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Información General</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Apertura:</span>
                        <p>{{ $record->opening_datetime ? $record->opening_datetime->format('d/m/Y H:i') : 'No disponible' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Cierre:</span>
                        <p>{{ $record->closing_datetime ? $record->closing_datetime->format('d/m/Y H:i') : 'En curso' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Usuario:</span>
                        <p>{{ $record->user->name ?? 'No asignado' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Estado:</span>
                        <p>{{ $record->closing_datetime ? 'Cerrada' : 'Abierta' }}</p>
                    </div>
                </div>
            </div>

            <!-- Resumen de Montos -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Resumen de Montos</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Monto Inicial:</span>
                        <p class="font-medium">S/ {{ number_format($record->opening_amount, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Monto Final:</span>
                        <p class="font-medium">S/ {{ number_format($record->actual_amount, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Total Movimientos:</span>
                        <p class="font-medium">S/ {{ number_format($movements->sum('amount'), 2) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Total Ventas:</span>
                        <p class="font-medium">S/ {{ number_format($orders->sum('total'), 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movimientos de Caja -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium">Movimientos de Caja</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($movements ?? [] as $movement)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $movement->created_at ? $movement->created_at->format('d/m/Y H:i') : 'No disponible' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $movement->movement_type ?? 'No especificado' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">S/ {{ number_format($movement->amount ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $movement->reason ?? 'Sin motivo' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $movement->approvedByUser->name ?? 'No asignado' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ventas -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium">Ventas</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Orden</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders ?? [] as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order->order_datetime ? $order->order_datetime->format('d/m/Y H:i') : 'No disponible' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order->id ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">S/ {{ number_format($order->total ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($order->payments && $order->payments->count() > 0)
                                    @foreach($order->payments as $payment)
                                        {{ $payment->payment_method }}@if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    No especificado
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order->user->name ?? 'No asignado' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::section>
