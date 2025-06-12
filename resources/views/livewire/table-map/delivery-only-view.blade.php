<div>
    <!-- Cargar el CSS mejorado -->
    <link rel="stylesheet" href="{{ asset('css/table-map-improved.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/delivery-view.css') }}?v={{ time() }}">

    <div class="delivery-container">
        <!-- Cabecera de la página -->
        <div class="delivery-page-header">
            <h1 class="delivery-page-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                Mis Pedidos de Delivery
            </h1>
            <div class="header-actions">
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="action-button logout-button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="delivery-stats">
            <div class="stat-card assigned">
                <div class="stat-value">{{ $this->getAssignedCount() }}</div>
                <div class="stat-label">Asignados</div>
            </div>
            <div class="stat-card in-transit">
                <div class="stat-value">{{ $this->getInTransitCount() }}</div>
                <div class="stat-label">En Tránsito</div>
            </div>
            <div class="stat-card delivered">
                <div class="stat-value">{{ $this->getDeliveredCount() }}</div>
                <div class="stat-label">Entregados</div>
            </div>
        </div>

        <!-- Sección de pedidos -->
        <div class="delivery-orders-section">
            <div class="delivery-section-header">
                <h2 class="delivery-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    Mis Pedidos Asignados ({{ $deliveryOrders->count() }})
                </h2>
            </div>

            @if($deliveryOrders->isEmpty())
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    <h3 class="empty-state-title">No tienes pedidos asignados</h3>
                    <p class="empty-state-message">Cuando te asignen pedidos, aparecerán aquí.</p>
                </div>
            @else
                <div class="delivery-orders-grid">
                    @foreach($deliveryOrders as $delivery)
                    @php
                        $statusInfo = $this->getDeliveryStatusInfo($delivery->status);
                        $customer = $delivery->order->customer ?? null;

                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'assigned' => 'Asignado',
                            'in_transit' => 'En Tránsito',
                            'delivered' => 'Entregado',
                            'cancelled' => 'Cancelado',
                        ];

                        $statusIcons = [
                            'pending' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                            'assigned' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
                            'in_transit' => '<path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />',
                            'delivered' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
                            'cancelled' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
                        ];
                    @endphp
                    <div class="delivery-card {{ $delivery->status }}">
                        <!-- Cabecera de la tarjeta -->
                        <div class="delivery-card-header">
                            <div>
                                <h3 class="order-number">Pedido #{{ $delivery->order_id }}</h3>
                                <p class="order-time">{{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="status-badge {{ $delivery->status }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    {!! $statusIcons[$delivery->status] ?? '' !!}
                                </svg>
                                {{ $statusLabels[$delivery->status] ?? 'Desconocido' }}
                            </span>
                        </div>

                        <!-- Contenido de la tarjeta -->
                        <div class="delivery-card-content">
                            <div class="info-group">
                                <span class="info-label">Cliente:</span>
                                <span class="info-value">{{ $customer ? $customer->name : 'Sin cliente' }}</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value">{{ $customer ? $customer->phone : 'Sin teléfono' }}</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value">{{ $delivery->delivery_address }}</span>
                            </div>

                            @if($delivery->delivery_references)
                                <div class="info-group">
                                    <span class="info-label">Referencias:</span>
                                    <span class="info-value">{{ $delivery->delivery_references }}</span>
                                </div>
                            @endif

                            @if($delivery->estimated_delivery_time)
                                <div class="info-group">
                                    <span class="info-label">Entrega estimada:</span>
                                    <span class="info-value">{{ $delivery->estimated_delivery_time->format('H:i') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Acciones de la tarjeta -->
                        <div class="delivery-card-actions">
                            @php
                                // Verificar si el usuario actual es el repartidor asignado a este pedido
                                $user = \Illuminate\Support\Facades\Auth::user();
                                $employee = \App\Models\Employee::where('user_id', $user->id)->first();
                                $isAssignedDeliveryPerson = $employee && $delivery->delivery_person_id === $employee->id;
                            @endphp

                            @if($delivery->status === 'assigned' && $isAssignedDeliveryPerson)
                                <button wire:click="updateDeliveryStatus({{ $delivery->id }}, 'in_transit')" class="action-button transit-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    Iniciar Entrega
                                </button>
                            @elseif($delivery->status === 'in_transit' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('cashier')))
                                <button wire:click="updateDeliveryStatus({{ $delivery->id }}, 'delivered')" class="action-button deliver-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Marcar Entregado
                                </button>
                            @endif

                            @if(!in_array($delivery->status, ['delivered', 'cancelled']) && $isAssignedDeliveryPerson)
                                <button wire:click="openCancelModal({{ $delivery->id }})" class="action-button cancel-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancelar
                                </button>
                            @endif

                            <a href="{{ route('delivery.order.details', ['orderId' => $delivery->order_id]) }}" class="action-button view-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Modales para asignar repartidor y cancelar pedido -->
    <div id="assignDeliveryPersonModal" class="delivery-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Asignar Repartidor</h2>
                <button type="button" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="deliveryPersonSelect" class="form-label">Seleccionar Repartidor:</label>
                    <select id="deliveryPersonSelect" class="form-control">
                        <option value="">Seleccionar...</option>
                        @foreach(\App\Models\Employee::where('position', 'Delivery')->get() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="action-button view-button close-modal">Cancelar</button>
                <button type="button" id="assignDeliveryPersonBtn" class="action-button transit-button">Asignar</button>
            </div>
        </div>
    </div>

    <div id="cancelDeliveryModal" class="delivery-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Cancelar Pedido</h2>
                <button type="button" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="cancellationReason" class="form-label">Motivo de Cancelación:</label>
                    <textarea id="cancellationReason" class="form-control" rows="3" placeholder="Ingrese el motivo de la cancelación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="action-button view-button close-modal">Cancelar</button>
                <button type="button" id="confirmCancelDeliveryBtn" class="action-button cancel-button">Confirmar Cancelación</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables para los modales
            const assignModal = document.getElementById('assignDeliveryPersonModal');
            const cancelModal = document.getElementById('cancelDeliveryModal');
            const closeButtons = document.querySelectorAll('.close-button, .close-modal');
            let currentDeliveryId = null;

            // Función para cerrar todos los modales
            function closeAllModals() {
                assignModal.style.display = 'none';
                cancelModal.style.display = 'none';
            }

            // Cerrar modales al hacer clic en botones de cierre
            closeButtons.forEach(button => {
                button.addEventListener('click', closeAllModals);
            });

            // Cerrar modales al hacer clic fuera de ellos
            window.addEventListener('click', function(event) {
                if (event.target === assignModal || event.target === cancelModal) {
                    closeAllModals();
                }
            });

            // Cerrar modales al presionar Escape
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeAllModals();
                }
            });

            // Escuchar eventos de Livewire
            window.addEventListener('openAssignDeliveryPersonModal', event => {
                currentDeliveryId = event.detail;
                assignModal.style.display = 'block';
            });

            window.addEventListener('openCancelDeliveryModal', event => {
                currentDeliveryId = event.detail;
                cancelModal.style.display = 'block';
                document.getElementById('cancellationReason').focus();
            });

            // Asignar repartidor
            document.getElementById('assignDeliveryPersonBtn').addEventListener('click', function() {
                const employeeId = document.getElementById('deliveryPersonSelect').value;
                if (!employeeId) {
                    showNotification('Por favor, seleccione un repartidor', 'error');
                    return;
                }

                if (employeeId && currentDeliveryId) {
                    // Llamar al método de Livewire
                    Livewire.dispatch('assignDeliveryPerson', [currentDeliveryId, employeeId]);
                    closeAllModals();
                    showNotification('Repartidor asignado correctamente', 'success');
                }
            });

            // Cancelar pedido
            document.getElementById('confirmCancelDeliveryBtn').addEventListener('click', function() {
                const reason = document.getElementById('cancellationReason').value;
                if (!reason.trim()) {
                    showNotification('Por favor, ingrese un motivo para la cancelación', 'error');
                    return;
                }

                if (currentDeliveryId) {
                    // Llamar al método de Livewire
                    Livewire.dispatch('cancelDelivery', [currentDeliveryId, reason]);
                    closeAllModals();
                    showNotification('Pedido cancelado correctamente', 'success');
                }
            });

            // Función para mostrar notificaciones
            function showNotification(message, type = 'info') {
                const notification = document.getElementById('notification');
                const messageElement = document.getElementById('notification-message');

                messageElement.textContent = message;
                notification.className = 'notification ' + type;
                notification.style.display = 'block';

                setTimeout(function() {
                    notification.style.display = 'none';
                }, 3000);
            }

            // Escuchar eventos de notificación de Livewire
            window.addEventListener('notification', event => {
                showNotification(event.detail.message, event.detail.type || 'info');
            });

            // Actualizar los pedidos cada minuto
            // TEMPORALMENTE DESHABILITADO: Auto-actualización
            /*
            setInterval(() => {
                try {
                    // Comprobar si estamos usando Livewire 2 o Livewire 3
                    if (typeof Livewire !== 'undefined') {
                        if (typeof Livewire.emit === 'function') {
                            // Livewire 2
                            Livewire.emit('refresh');
                        } else if (typeof Livewire.dispatch === 'function') {
                            // Livewire 3
                            Livewire.dispatch('refresh');
                        }
                    }
                } catch (e) {
                    console.error('Error al actualizar los pedidos:', e);
                }
            }, 60000); // 60 segundos
            */
        });
    </script>

    <!-- Notificación -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <span id="notification-message"></span>
            <button type="button" class="notification-close" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
        </div>
    </div>
</div>
