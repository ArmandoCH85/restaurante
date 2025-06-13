<div class="bg-white rounded-lg shadow p-4">
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b pb-2">
            <span class="text-sm font-semibold text-gray-600">
                Desglose por Tipo de Servicio
            </span>
        </div>

        <div class="grid gap-4">
            @foreach($types as $type)
                <div class="flex items-center justify-between p-2 rounded-lg bg-{{ $type['color'] }}-50">
                    <span class="inline-flex items-center text-sm font-medium text-{{ $type['color'] }}-700">
                        <x-dynamic-component :component="$type['icon']" class="w-5 h-5 mr-2" />
                        {{ $type['label'] }}
                    </span>
                    <span class="text-sm font-bold text-{{ $type['color'] }}-700">
                        S/ {{ number_format($type['amount'], 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
