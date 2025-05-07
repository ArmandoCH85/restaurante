@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Facades\FilamentView;
@endphp

<x-filament::layouts.card>
    <div class="delivery-dashboard">
        <!-- Cabecera con título y estadísticas -->
        <div class="delivery-header">
            <h1 class="delivery-title">
                <x-filament::icon
                    alias="delivery"
                    icon="heroicon-o-truck"
                    class="fi-header-heading-icon h-6 w-6 text-delivery-600"
                />
                Mis Pedidos de Delivery
            </h1>
            
            <!-- Estadísticas rápidas -->
            <div class="delivery-stats">
                <div class="stat-card assigned">
                    <span class="stat-value">{{ $this->getAssignedCount() }}</span>
                    <span class="stat-label">Asignados</span>
                </div>
                <div class="stat-card in-transit">
                    <span class="stat-value">{{ $this->getInTransitCount() }}</span>
                    <span class="stat-label">En Tránsito</span>
                </div>
                <div class="stat-card delivered">
                    <span class="stat-value">{{ $this->getDeliveredCount() }}</span>
                    <span class="stat-label">Entregados</span>
                </div>
            </div>
        </div>
        
        <!-- Filtros y búsqueda -->
        <div class="delivery-filters">
            <div class="filter-group">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.500ms="tableSearchQuery"
                        placeholder="Buscar pedidos..."
                    />
                </x-filament::input.wrapper>
            </div>
            
            <div class="filter-group">
                <x-filament::input.wrapper>
                    <x-filament::input.select
                        wire:model.live="tableFilters.status.value"
                    >
                        <option value="">Todos los estados</option>
                        <option value="assigned">Asignados</option>
                        <option value="in_transit">En Tránsito</option>
                        <option value="delivered">Entregados</option>
                        <option value="cancelled">Cancelados</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
        
        <!-- Contenedor de pedidos -->
        <div class="delivery-orders-container">
            @if($this->records->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <x-filament::icon
                            alias="empty"
                            icon="heroicon-o-inbox"
                            class="h-16 w-16 text-gray-300"
                        />
                    </div>
                    <h3 class="empty-state-heading">No tienes pedidos asignados</h3>
                    <p class="empty-state-description">Cuando te asignen pedidos, aparecerán aquí.</p>
                </div>
            @else
                <div class="delivery-orders-grid">
                    @foreach($this->records as $record)
                        <div class="delivery-card {{ $record->status }}">
                            <!-- Cabecera del pedido -->
                            <div class="delivery-card-header">
                                <div class="order-info">
                                    <h3 class="order-number">Pedido #{{ $record->order_id }}</h3>
                                    <span class="order-time">{{ $record->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge {{ $record->status }}">
                                        @php
                                            $statusLabels = [
                                                'pending' => 'Pendiente',
                                                'assigned' => 'Asignado',
                                                'in_transit' => 'En Tránsito',
                                                'delivered' => 'Entregado',
                                                'cancelled' => 'Cancelado',
                                            ];
                                            $statusIcons = [
                                                'pending' => 'heroicon-o-clock',
                                                'assigned' => 'heroicon-o-user',
                                                'in_transit' => 'heroicon-o-truck',
                                                'delivered' => 'heroicon-o-check-circle',
                                                'cancelled' => 'heroicon-o-x-circle',
                                            ];
                                        @endphp
                                        <x-filament::icon
                                            :icon="$statusIcons[$record->status] ?? 'heroicon-o-question-mark-circle'"
                                            class="h-4 w-4"
                                        />
                                        {{ $statusLabels[$record->status] ?? 'Desconocido' }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Contenido del pedido -->
                            <div class="delivery-card-content">
                                <!-- Información del cliente -->
                                <div class="customer-info">
                                    <div class="info-group">
                                        <span class="info-label">Cliente:</span>
                                        <span class="info-value">{{ $record->order->customer->name ?? 'Sin cliente' }}</span>
                                    </div>
                                    <div class="info-group">
                                        <span class="info-label">Teléfono:</span>
                                        <span class="info-value">{{ $record->order->customer->phone ?? 'Sin teléfono' }}</span>
                                    </div>
                                </div>
                                
                                <!-- Dirección de entrega -->
                                <div class="address-info">
                                    <div class="info-group">
                                        <span class="info-label">Dirección:</span>
                                        <span class="info-value">{{ $record->delivery_address }}</span>
                                    </div>
                                    @if($record->delivery_references)
                                        <div class="info-group">
                                            <span class="info-label">Referencias:</span>
                                            <span class="info-value">{{ $record->delivery_references }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Tiempo estimado -->
                                @if($record->estimated_delivery_time)
                                    <div class="time-info">
                                        <div class="info-group">
                                            <span class="info-label">Entrega estimada:</span>
                                            <span class="info-value">{{ $record->estimated_delivery_time->format('H:i') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Acciones del pedido -->
                            <div class="delivery-card-actions">
                                @if($record->status === 'assigned')
                                    <button 
                                        type="button"
                                        wire:click="updateDeliveryStatus({{ $record->id }}, 'in_transit')"
                                        class="action-button transit-action"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-o-truck"
                                            class="h-5 w-5"
                                        />
                                        Iniciar Entrega
                                    </button>
                                @elseif($record->status === 'in_transit')
                                    <button 
                                        type="button"
                                        wire:click="updateDeliveryStatus({{ $record->id }}, 'delivered')"
                                        class="action-button deliver-action"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-o-check-circle"
                                            class="h-5 w-5"
                                        />
                                        Marcar Entregado
                                    </button>
                                @endif
                                
                                @if(!in_array($record->status, ['delivered', 'cancelled']))
                                    <button 
                                        type="button"
                                        wire:click="openCancelModal({{ $record->id }})"
                                        class="action-button cancel-action"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-o-x-circle"
                                            class="h-5 w-5"
                                        />
                                        Cancelar
                                    </button>
                                @endif
                                
                                <a 
                                    href="{{ route('pos.index', ['order_id' => $record->order_id, 'preserve_cart' => 'true']) }}"
                                    class="action-button view-action"
                                    target="_blank"
                                >
                                    <x-filament::icon
                                        icon="heroicon-o-eye"
                                        class="h-5 w-5"
                                    />
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal de cancelación -->
    <x-filament::modal
        id="cancel-delivery-modal"
        width="md"
        :heading="__('Cancelar Pedido')"
        :description="__('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')"
        :open="$this->isCancelModalOpen"
        x-on:close-modal="$wire.closeCancelModal()"
    >
        <div class="space-y-4">
            <x-filament::input.wrapper>
                <x-filament::input.label for="cancellation_reason">
                    {{ __('Motivo de Cancelación') }}
                </x-filament::input.label>
                
                <x-filament::input.textarea
                    id="cancellation_reason"
                    wire:model="cancellationReason"
                    placeholder="Ingresa el motivo de la cancelación..."
                    rows="3"
                />
            </x-filament::input.wrapper>
        </div>
        
        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button
                    color="gray"
                    x-on:click="$wire.closeCancelModal()"
                >
                    {{ __('Cancelar') }}
                </x-filament::button>
                
                <x-filament::button
                    color="danger"
                    wire:click="confirmCancel"
                >
                    {{ __('Confirmar Cancelación') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament::layouts.card>

<style>
    /* Estilos para la vista de pedidos de delivery */
    .delivery-dashboard {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    /* Cabecera */
    .delivery-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgb(229, 231, 235);
    }
    
    .delivery-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: rgb(17, 24, 39);
    }
    
    .dark .delivery-title {
        color: rgb(243, 244, 246);
    }
    
    /* Estadísticas */
    .delivery-stats {
        display: flex;
        gap: 1rem;
    }
    
    .stat-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        background-color: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        min-width: 100px;
        border-top: 3px solid transparent;
    }
    
    .dark .stat-card {
        background-color: rgb(31, 41, 55);
    }
    
    .stat-card.assigned {
        border-top-color: rgb(79, 70, 229);
    }
    
    .stat-card.in-transit {
        border-top-color: rgb(245, 158, 11);
    }
    
    .stat-card.delivered {
        border-top-color: rgb(16, 185, 129);
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .stat-card.assigned .stat-value {
        color: rgb(79, 70, 229);
    }
    
    .stat-card.in-transit .stat-value {
        color: rgb(245, 158, 11);
    }
    
    .stat-card.delivered .stat-value {
        color: rgb(16, 185, 129);
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: rgb(107, 114, 128);
        margin-top: 0.25rem;
    }
    
    /* Filtros */
    .delivery-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        padding-bottom: 1rem;
    }
    
    .filter-group {
        min-width: 200px;
        flex: 1;
    }
    
    /* Contenedor de pedidos */
    .delivery-orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }
    
    /* Tarjeta de pedido */
    .delivery-card {
        display: flex;
        flex-direction: column;
        background-color: white;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        border-left: 4px solid rgb(107, 114, 128);
    }
    
    .dark .delivery-card {
        background-color: rgb(31, 41, 55);
    }
    
    .delivery-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .delivery-card.assigned {
        border-left-color: rgb(79, 70, 229);
    }
    
    .delivery-card.in_transit {
        border-left-color: rgb(245, 158, 11);
    }
    
    .delivery-card.delivered {
        border-left-color: rgb(16, 185, 129);
    }
    
    .delivery-card.cancelled {
        border-left-color: rgb(239, 68, 68);
    }
    
    /* Cabecera de la tarjeta */
    .delivery-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgb(243, 244, 246);
    }
    
    .dark .delivery-card-header {
        border-bottom-color: rgb(55, 65, 81);
    }
    
    .order-info {
        display: flex;
        flex-direction: column;
    }
    
    .order-number {
        font-weight: 600;
        font-size: 0.875rem;
        color: rgb(17, 24, 39);
        margin: 0;
    }
    
    .dark .order-number {
        color: rgb(243, 244, 246);
    }
    
    .order-time {
        font-size: 0.75rem;
        color: rgb(107, 114, 128);
    }
    
    /* Badge de estado */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: rgb(243, 244, 246);
        color: rgb(55, 65, 81);
    }
    
    .status-badge.assigned {
        background-color: rgba(79, 70, 229, 0.1);
        color: rgb(79, 70, 229);
    }
    
    .status-badge.in_transit {
        background-color: rgba(245, 158, 11, 0.1);
        color: rgb(245, 158, 11);
    }
    
    .status-badge.delivered {
        background-color: rgba(16, 185, 129, 0.1);
        color: rgb(16, 185, 129);
    }
    
    .status-badge.cancelled {
        background-color: rgba(239, 68, 68, 0.1);
        color: rgb(239, 68, 68);
    }
    
    /* Contenido de la tarjeta */
    .delivery-card-content {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
    }
    
    .customer-info, .address-info, .time-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .info-group {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(107, 114, 128);
    }
    
    .info-value {
        font-size: 0.875rem;
        color: rgb(17, 24, 39);
    }
    
    .dark .info-value {
        color: rgb(243, 244, 246);
    }
    
    /* Acciones de la tarjeta */
    .delivery-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-top: 1px solid rgb(243, 244, 246);
        background-color: rgb(249, 250, 251);
    }
    
    .dark .delivery-card-actions {
        border-top-color: rgb(55, 65, 81);
        background-color: rgb(17, 24, 39);
    }
    
    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.2s ease;
        flex: 1;
        min-width: 100px;
        border: none;
        cursor: pointer;
    }
    
    .action-button:hover {
        transform: translateY(-1px);
    }
    
    .transit-action {
        background-color: rgb(79, 70, 229);
        color: white;
    }
    
    .transit-action:hover {
        background-color: rgb(67, 56, 202);
    }
    
    .deliver-action {
        background-color: rgb(16, 185, 129);
        color: white;
    }
    
    .deliver-action:hover {
        background-color: rgb(5, 150, 105);
    }
    
    .cancel-action {
        background-color: rgb(239, 68, 68);
        color: white;
    }
    
    .cancel-action:hover {
        background-color: rgb(220, 38, 38);
    }
    
    .view-action {
        background-color: rgb(107, 114, 128);
        color: white;
        text-decoration: none;
    }
    
    .view-action:hover {
        background-color: rgb(75, 85, 99);
    }
    
    /* Estado vacío */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 1.5rem;
        text-align: center;
    }
    
    .empty-state-icon {
        margin-bottom: 1rem;
    }
    
    .empty-state-heading {
        font-size: 1.125rem;
        font-weight: 600;
        color: rgb(17, 24, 39);
        margin-bottom: 0.5rem;
    }
    
    .dark .empty-state-heading {
        color: rgb(243, 244, 246);
    }
    
    .empty-state-description {
        font-size: 0.875rem;
        color: rgb(107, 114, 128);
        max-width: 20rem;
        margin: 0 auto;
    }
    
    /* Responsive */
    @media (max-width: 640px) {
        .delivery-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .delivery-stats {
            width: 100%;
            justify-content: space-between;
        }
        
        .stat-card {
            min-width: 80px;
        }
        
        .delivery-orders-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
