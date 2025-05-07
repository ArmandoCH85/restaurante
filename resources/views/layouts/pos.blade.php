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

        // Funciones de utilidad para la interfaz POS
        document.addEventListener('livewire:init', () => {
            // Escuchar eventos de notificación emitidos por Livewire
            Livewire.on('notification', (data) => {
                // Usar SweetAlert2 para notificaciones mejoradas
                const timeout = data.timeout || 3000; // Tiempo predeterminado: 3 segundos

                // Configurar el icono según el tipo de notificación
                const icon = data.type === 'success' ? 'success' :
                             data.type === 'error' ? 'error' :
                             data.type === 'warning' ? 'warning' : 'info';

                // Mostrar notificación con SweetAlert2
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: timeout,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: icon,
                    title: data.title || '',
                    text: data.message || '',
                    background: data.type === 'success' ? '#ecfdf5' :
                                data.type === 'error' ? '#fef2f2' :
                                data.type === 'warning' ? '#fffbeb' : '#eff6ff',
                    color: '#374151'
                });

                // Para notificaciones importantes, también mostrar una alerta modal si se especifica
                if (data.showModal) {
                    setTimeout(() => {
                        Swal.fire({
                            icon: icon,
                            title: data.title || '',
                            text: data.message || '',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: data.type === 'success' ? '#10b981' :
                                              data.type === 'error' ? '#ef4444' :
                                              data.type === 'warning' ? '#f59e0b' : '#3b82f6'
                        });
                    }, 500); // Pequeño retraso para que no se superpongan
                }
            });
        });
    </script>
</body>
</html>
