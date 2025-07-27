<div class="relative h-[500px] bg-gray-50 dark:bg-gray-900 overflow-hidden rounded-lg">
    {{-- Contenedor SVG para el mapa de mesas --}}
    <svg class="w-full h-full" viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg">
        {{-- Áreas de ubicación con colores sutiles --}}
        <g class="areas">
            <rect x="10" y="10" width="250" height="480" rx="5" fill="#f0fff4" stroke="#0c4a6e" stroke-width="1" stroke-dasharray="5,5" />
            <text x="20" y="30" fill="#0c4a6e" font-size="14">Interior</text>
            
            <rect x="270" y="10" width="250" height="480" rx="5" fill="#f1f5f9" stroke="#0c4a6e" stroke-width="1" stroke-dasharray="5,5" />
            <text x="280" y="30" fill="#0c4a6e" font-size="14">Exterior</text>
            
            <rect x="530" y="10" width="260" height="480" rx="5" fill="#fef3c7" stroke="#0c4a6e" stroke-width="1" stroke-dasharray="5,5" />
            <text x="540" y="30" fill="#0c4a6e" font-size="14">Terraza</text>
        </g>

        {{-- Grupo de mesas --}}
        <g class="tables">
            @foreach ($tables as $table)
                @php
                    $tablePosition = $tablePositions[$table->id] ?? ['x' => 50, 'y' => 50, 'rotation' => 0];
                    $statusColor = match($table->status) {
                        'available' => '#10b981', // emerald-500
                        'reserved' => '#3b82f6',  // blue-500
                        'occupied' => '#f59e0b',  // amber-500
                        'prebill' => '#06b6d4',   // cyan-500
                        'maintenance' => '#6b7280', // gray-500
                        default => '#6b7280'
                    };
                    $tableWidth = 40 + ($table->capacity * 3); // Tamaño proporcional a la capacidad
                    $tableHeight = min(40, 30 + ($table->capacity * 2));
                @endphp
                
                <g 
                    wire:key="layout-table-{{ $table->id }}"
                    transform="translate({{ $tablePosition['x'] }},{{ $tablePosition['y'] }}) rotate({{ $tablePosition['rotation'] }})"
                    @if ($isEditingLayout)
                        class="cursor-move"
                        x-data="tableDraggable"
                    @else
                        class="cursor-pointer"
                        wire:click="selectTable({{ $table->id }})"
                    @endif
                    @class([
                        'hover:opacity-80',
                        'outline-primary-500 outline-offset-2 outline-2' => $selectedTableId == $table->id
                    ])
                >
                    {{-- Mesa rectangular con forma de acuerdo a la proporción áurea --}}
                    <rect 
                        x="{{ -$tableWidth / 2 }}"
                        y="{{ -$tableHeight / 2 }}"
                        width="{{ $tableWidth }}"
                        height="{{ $tableHeight }}"
                        rx="4"
                        stroke="{{ $statusColor }}"
                        stroke-width="2"
                        fill="white"
                        fill-opacity="0.8"
                    />
                    
                    {{-- Número de mesa --}}
                    <text 
                        x="0"
                        y="0"
                        text-anchor="middle"
                        dominant-baseline="middle"
                        fill="#374151"
                        font-weight="500"
                    >
                        {{ $table->number }}
                    </text>
                    
                    {{-- Indicadores de personas --}}
                    <g transform="translate(0, {{ $tableHeight / 2 - 8 }})">
                        @for ($i = 0; $i < min(8, $table->capacity); $i++)
                            <circle 
                                cx="{{ ($i * 6) - (min(8, $table->capacity) * 3) + 3 }}" 
                                cy="0" 
                                r="2" 
                                fill="#374151" 
                            />
                        @endfor
                    </g>
                    
                    {{-- Indicadores de reservas o pedidos --}}
                    @if($table->activeReservations && $table->activeReservations->count() > 0)
                        <circle cx="{{ ($tableWidth / 2) - 8 }}" cy="{{ -($tableHeight / 2) + 8 }}" r="6" fill="#3b82f6" />
                        <text 
                            x="{{ ($tableWidth / 2) - 8 }}" 
                            y="{{ -($tableHeight / 2) + 8 }}" 
                            text-anchor="middle" 
                            dominant-baseline="middle" 
                            fill="white"
                            font-size="8"
                        >
                            {{ min(9, $table->activeReservations->count()) }}
                        </text>
                    @endif
                    
                    @if($table->activeOrders && $table->activeOrders->count() > 0)
                        <circle cx="{{ ($tableWidth / 2) - 8 }}" cy="{{ -($tableHeight / 2) + 24 }}" r="6" fill="#a855f7" />
                        <text 
                            x="{{ ($tableWidth / 2) - 8 }}" 
                            y="{{ -($tableHeight / 2) + 24 }}" 
                            text-anchor="middle" 
                            dominant-baseline="middle" 
                            fill="white"
                            font-size="8"
                        >
                            {{ min(9, $table->activeOrders->count()) }}
                        </text>
                    @endif
                </g>
            @endforeach
        </g>
    </svg>
    
    {{-- Mensaje de modo edición --}}
    @if ($isEditingLayout)
        <div class="absolute top-3 left-0 right-0 text-center">
            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-md text-sm">
                Modo edición: Arrastra las mesas para organizarlas. Haz clic en "Guardar" cuando termines.
            </span>
        </div>
    @endif
</div>
