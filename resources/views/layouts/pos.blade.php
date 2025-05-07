<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                // Mostrar notificación con el mensaje recibido
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg max-w-sm z-50 transform transition-all duration-500 ${data.type === 'success' ? 'bg-green-500' : data.type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-white`;

                notification.innerHTML = `
                    <div class="flex items-center">
                        <span class="mr-2">
                            ${data.type === 'success'
                                ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>'
                                : data.type === 'error'
                                ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>'
                                : '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                            }
                        </span>
                        <div>
                            <p class="font-medium">${data.title || ''}</p>
                            <p class="text-sm">${data.message || ''}</p>
                        </div>
                    </div>
                `;

                document.body.appendChild(notification);

                // Eliminar la notificación después de 3 segundos
                setTimeout(() => {
                    notification.classList.add('opacity-0', 'translate-x-full');
                    setTimeout(() => notification.remove(), 500);
                }, 3000);
            });
        });
    </script>
</body>
</html>
