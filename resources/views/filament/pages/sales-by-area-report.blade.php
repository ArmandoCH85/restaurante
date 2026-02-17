<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section>
            <p class="text-sm text-gray-600">
                Este reporte solo considera productos con area asignada.
            </p>
        </x-filament::section>

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        <x-filament::section>
            {{ $this->table }}
        </x-filament::section>

        @if (!empty($this->drilldownRows))
            <x-filament::section>
                <div class="mb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Productos vendidos
                    </h3>
                    <p class="text-sm text-gray-600">
                        Area: {{ $this->drilldownAreaName }} | Periodo: {{ $this->drilldownPeriod }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Producto</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Unidades</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($this->drilldownRows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-800">
                                        {{ $row['product_code'] }} - {{ $row['product_name'] }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-800">
                                        {{ number_format($row['units_sold'], 3) }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-800">
                                        S/ {{ number_format($row['net_sold'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

