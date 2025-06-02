@props([
    'status' => 'pending',
    'size' => 'md',
    'animate' => false,
    'showLabel' => false
])

@php
    // Mapeo de estados a etiquetas en español
    $statusLabels = [
        'pending' => 'Pendiente',
        'assigned' => 'Asignado',
        'in_transit' => 'En Tránsito',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado'
    ];

    // Configuración de tamaños
    $sizes = [
        'xs' => [
            'container' => 'w-6 h-20',
            'light' => 'w-3 h-3',
            'padding' => 'p-1',
            'gap' => 'gap-0.5'
        ],
        'sm' => [
            'container' => 'w-8 h-24',
            'light' => 'w-4 h-4',
            'padding' => 'p-1.5',
            'gap' => 'gap-1'
        ],
        'md' => [
            'container' => 'w-10 h-32',
            'light' => 'w-6 h-6',
            'padding' => 'p-2',
            'gap' => 'gap-1.5'
        ],
        'lg' => [
            'container' => 'w-12 h-40',
            'light' => 'w-8 h-8',
            'padding' => 'p-3',
            'gap' => 'gap-2'
        ],
        'xl' => [
            'container' => 'w-16 h-48',
            'light' => 'w-10 h-10',
            'padding' => 'p-4',
            'gap' => 'gap-3'
        ]
    ];

    $currentSize = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'delivery-traffic-light-component']) }}>
    <div class="traffic-light-container {{ $currentSize['container'] }} {{ $currentSize['padding'] }} {{ $currentSize['gap'] }}">
        <!-- Luz Roja - Pendiente -->
        <div class="traffic-light-light red {{ $currentSize['light'] }} {{ $status === 'pending' ? 'active' : '' }} {{ $animate && $status === 'pending' ? 'animate-pulse-red' : '' }}" 
             title="Pendiente"></div>
        
        <!-- Luz Naranja - Asignado -->
        <div class="traffic-light-light orange {{ $currentSize['light'] }} {{ $status === 'assigned' ? 'active' : '' }}" 
             title="Asignado"></div>
        
        <!-- Luz Amarilla - En Tránsito -->
        <div class="traffic-light-light yellow {{ $currentSize['light'] }} {{ $status === 'in_transit' ? 'active' : '' }} {{ $animate && $status === 'in_transit' ? 'animate-pulse-yellow' : '' }}" 
             title="En Tránsito"></div>
        
        <!-- Luz Verde - Entregado -->
        <div class="traffic-light-light green {{ $currentSize['light'] }} {{ $status === 'delivered' ? 'active' : '' }}" 
             title="Entregado"></div>
    </div>
    
    @if($showLabel)
        <div class="traffic-light-label">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                {{ $statusLabels[$status] ?? 'Desconocido' }}
            </span>
        </div>
    @endif
</div>

<style>
    /* Estilos del componente semáforo */
    .delivery-traffic-light-component {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .traffic-light-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: rgb(31, 41, 55);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        border: 2px solid rgb(55, 65, 81);
    }

    .dark .traffic-light-container {
        background-color: rgb(17, 24, 39);
        border-color: rgb(31, 41, 55);
    }

    .traffic-light-light {
        border-radius: 50%;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
        opacity: 0.3;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .traffic-light-light.active {
        opacity: 1;
        box-shadow: 
            0 0 8px currentColor,
            inset 0 1px 2px rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    /* Colores del semáforo */
    .traffic-light-light.red {
        background-color: #dc2626;
        color: #dc2626;
    }

    .traffic-light-light.orange {
        background-color: #ea580c;
        color: #ea580c;
    }

    .traffic-light-light.yellow {
        background-color: #eab308;
        color: #eab308;
    }

    .traffic-light-light.green {
        background-color: #16a34a;
        color: #16a34a;
    }

    .traffic-light-light.gray {
        background-color: #6b7280;
        color: #6b7280;
    }

    /* Animaciones */
    .animate-pulse-red {
        animation: pulse-red 2s infinite;
    }

    .animate-pulse-yellow {
        animation: pulse-yellow 1.5s infinite;
    }

    @keyframes pulse-red {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    @keyframes pulse-yellow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .traffic-light-label {
        text-align: center;
        margin-top: 0.25rem;
    }
</style>
