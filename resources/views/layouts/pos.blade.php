<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema POS - Restaurante</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Eliminar Tailwind CDN --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    {{-- Eliminar referencia a app.css y la configuración inline de Tailwind --}}
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    {{-- <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script> --}}

    <!-- Incluir assets compilados por Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- CSS personalizado para correcciones -->
    <link href="{{ asset('css/pos-fix.css') }}" rel="stylesheet">
    <link href="{{ asset('css/product-images.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pos-cart-improvements.css') }}?v={{ time() }}" rel="stylesheet">
    <link href="{{ asset('css/delivery-form-fix.css') }}?v={{ time() }}" rel="stylesheet">

    <!-- SweetAlert2 para notificaciones mejoradas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Estilos inline -->
    <style>
        [x-cloak] { display: none !important; }

        /* Estilos básicos */
        .category-active {
            background-color: #e6f0ff;
            color: #1e429f;
            font-weight: 500;
        }

        .dark .category-active {
            background-color: #1e429f;
            color: #bbd6ff;
        }

        /* Fix para placeholders */
        ::placeholder {
            color: #6b7280 !important;
            opacity: 1 !important;
        }

        .dark ::placeholder {
            color: #9ca3af !important;
            opacity: 1 !important;
        }

        /* Notificación personalizada */
        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 350px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: flex;
            align-items: flex-start;
            animation: slideIn 0.3s ease-out forwards;
            background-color: white;
            color: #333;
        }

        .dark .custom-notification {
            background-color: #1f2937;
            color: white;
        }

        .notification-success {
            border-left: 4px solid #10b981;
        }

        .notification-error {
            border-left: 4px solid #ef4444;
        }

        .notification-warning {
            border-left: 4px solid #f59e0b;
        }

        .notification-info {
            border-left: 4px solid #3b82f6;
        }

        .notification-icon {
            margin-right: 12px;
            flex-shrink: 0;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 16px;
        }

        .notification-message {
            font-size: 14px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>

    @livewireStyles
</head>
<body class="antialiased bg-gray-100 text-gray-900 h-full overflow-hidden dark:bg-gray-900 dark:text-gray-100">
    <div class="min-h-screen flex flex-col">
        @yield('content')
    </div>

    @livewireScripts
    <script src="{{ asset('js/pos-modals.js') }}"></script>
    <script src="{{ asset('js/pos-refresh.js') }}?v={{ time() }}"></script>
    <script>
        // Detectar preferencia de modo oscuro
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

        // Detectar cambios en el modo oscuro/claro
        const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        darkModeMediaQuery.addEventListener('change', (e) => {
            if (e.matches && !localStorage.theme) {
                // Cambió a modo oscuro y no hay preferencia guardada
                document.documentElement.classList.add('dark');
            } else if (!e.matches && !localStorage.theme) {
                // Cambió a modo claro y no hay preferencia guardada
                document.documentElement.classList.remove('dark');
            }
        });

        // Funciones de utilidad para la interfaz POS
        document.addEventListener('DOMContentLoaded', function() {
            // Solución directa para notificaciones
            function handleNotification(data) {
                console.log('Notificación recibida:', data);
                const timeout = data.timeout || 3000;

                // Crear notificación personalizada
                showCustomNotification(data.type, data.title, data.message, timeout);

                // Para notificaciones importantes, también mostrar una alerta modal si se especifica
                if (data.showModal) {
                    setTimeout(() => {
                        Swal.fire({
                            icon: data.type,
                            title: data.title || '',
                            html: data.message || '',
                            confirmButtonText: 'Aceptar'
                        });
                    }, 500);
                }
            }

            // Método 1: Escuchar eventos de Livewire 2
            if (window.Livewire) {
                console.log('Configurando listeners de Livewire');
                window.Livewire.on('notification', handleNotification);
            }

            // Método 2: Escuchar eventos de Livewire 3
            document.addEventListener('livewire:initialized', function() {
                console.log('Livewire inicializado');

                // Escuchar eventos dispatch
                Livewire.hook('message.processed', (message, component) => {
                    console.log('Mensaje procesado:', message);

                    if (message.response && message.response.effects && message.response.effects.dispatches) {
                        console.log('Dispatches encontrados:', message.response.effects.dispatches);

                        message.response.effects.dispatches.forEach(dispatch => {
                            if (dispatch.event === 'notification') {
                                console.log('Evento notification encontrado:', dispatch);
                                handleNotification(dispatch.data);
                            }
                        });
                    }
                });
            });

            // Función para mostrar notificaciones personalizadas
            function showCustomNotification(type, title, message, timeout) {
                // Eliminar notificaciones anteriores
                const existingNotifications = document.querySelectorAll('.custom-notification');
                existingNotifications.forEach(notification => {
                    notification.style.animation = 'fadeOut 0.3s forwards';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });

                // Crear elemento de notificación
                const notification = document.createElement('div');
                notification.className = `custom-notification notification-${type}`;

                // Iconos según el tipo
                let iconSvg = '';
                switch(type) {
                    case 'success':
                        iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                        break;
                    case 'error':
                        iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                        break;
                    case 'warning':
                        iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
                        break;
                    default: // info
                        iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                }

                // Contenido de la notificación
                notification.innerHTML = `
                    <div class="notification-icon">${iconSvg}</div>
                    <div class="notification-content">
                        <div class="notification-title">${title || ''}</div>
                        <div class="notification-message">${message || ''}</div>
                    </div>
                `;

                // Agregar al DOM
                document.body.appendChild(notification);

                // Eliminar después del tiempo especificado
                setTimeout(() => {
                    notification.style.animation = 'fadeOut 0.3s forwards';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, timeout);
            }
        });
    </script>
</body>
</html>
