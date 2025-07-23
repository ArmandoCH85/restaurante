@php
    $status = $getState();

    // Mapeo de estados a etiquetas en español
    $statusLabels = [
        'pending' => 'Pendiente',
        'assigned' => 'Asignado',
        'in_transit' => 'En tránsito',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado'
    ];

    // Mapeo de estados a colores de badge de Filament
    $statusColors = [
        'pending' => 'warning',
        'assigned' => 'info',
        'in_transit' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];

    $statusLabel = $statusLabels[$status] ?? $status;
    $statusColor = $statusColors[$status] ?? 'gray';
@endphp

<div class="delivery-status-column" data-delivery-id="{{ $getRecord()->id }}">
    <div class="delivery-status-content">
        <!-- SEMÁFORO DE DELIVERY -->
        <div class="delivery-admin-traffic-light">
            <x-delivery-traffic-light
                :status="$status"
                size="xs"
                :animate="in_array($status, ['pending', 'in_transit'])"
                :showLabel="false"
                class="admin-semaphore"
            />
        </div>

        <!-- BADGE DE ESTADO -->
        <div class="delivery-status-badge">
            <x-filament::badge :color="$statusColor">
                {{ $statusLabel }}
            </x-filament::badge>
        </div>
    </div>
</div>

<style>
    /* Estilos para la columna de estado con semáforo en vista administrativa */
    .delivery-status-column {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        min-width: 100px;
    }

    .delivery-status-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
    }

    .delivery-admin-traffic-light {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .admin-semaphore {
        /* Tamaño más compacto para tabla administrativa */
        transform: scale(0.5);
        transform-origin: center;
    }

    .delivery-status-badge {
        flex: 1;
        display: flex;
        align-items: center;
    }

    /* Ajustes responsivos para la vista administrativa */
    @media (max-width: 768px) {
        .delivery-status-content {
            gap: 0.25rem;
        }

        .admin-semaphore {
            transform: scale(0.4);
        }
        
        .delivery-status-column {
            min-width: 80px;
        }
    }

    /* Asegurar que el semáforo sea visible en la tabla */
    .delivery-status-column .delivery-traffic-light-component {
        z-index: 1;
        position: relative;
    }

    /* Estados del semáforo con colores consistentes */
    .delivery-status-column .traffic-light-light.red.active {
        background-color: #dc2626 !important;
        box-shadow: 0 0 8px #dc2626 !important;
    }

    .delivery-status-column .traffic-light-light.orange.active {
        background-color: #ea580c !important;
        box-shadow: 0 0 8px #ea580c !important;
    }

    .delivery-status-column .traffic-light-light.yellow.active {
        background-color: #eab308 !important;
        box-shadow: 0 0 8px #eab308 !important;
    }

    .delivery-status-column .traffic-light-light.green.active {
        background-color: #16a34a !important;
        box-shadow: 0 0 8px #16a34a !important;
    }

    .delivery-status-column .traffic-light-light.gray.active {
        background-color: #6b7280 !important;
        box-shadow: 0 0 8px #6b7280 !important;
    }

    /* Integración con tabla de Filament */
    .fi-ta-table .delivery-status-column {
        position: relative;
        z-index: 1;
        min-width: 140px;
        padding: 0.5rem;
    }

    .fi-ta-table .delivery-status-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        justify-content: flex-start;
    }

    .fi-ta-table .delivery-admin-traffic-light {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
    }

    .fi-ta-table .admin-semaphore {
        transform: scale(0.7);
        transform-origin: center;
    }

    .fi-ta-table .delivery-status-badge {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    /* Asegurar que el semáforo sea visible en la tabla */
    .fi-ta-table .delivery-traffic-light-component {
        position: relative;
        z-index: 3;
        overflow: visible !important;
    }

    /* Ajustes responsivos para la tabla administrativa */
    @media (max-width: 1024px) {
        .fi-ta-table .delivery-status-content {
            gap: 0.5rem;
        }

        .fi-ta-table .admin-semaphore {
            transform: scale(0.6);
        }

        .fi-ta-table .delivery-status-column {
            min-width: 120px;
        }
    }

    @media (max-width: 768px) {
        .fi-ta-table .admin-semaphore {
            transform: scale(0.5);
        }

        .fi-ta-table .delivery-status-column {
            min-width: 100px;
        }
    }
</style>
