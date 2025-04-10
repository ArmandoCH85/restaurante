<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Mesas - Restaurante</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .filter-active {
            background-color: #EBF5FF;
            color: #1E3A8A;
            border-color: #93C5FD;
        }

        .dark .filter-active {
            background-color: #1E3A8A;
            color: #EBF5FF;
            border-color: #1E40AF;
        }

        /* Animaciones para las mesas */
        .table-card {
            transition: all 0.3s ease;
        }

        .table-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Para el estado ocupado */
        @keyframes pulse-subtle {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        .occupied {
            animation: pulse-subtle 2s infinite;
        }

        /* Para el modo oscuro */
        .dark .bg-white {
            background-color: #1F2937;
        }

        .dark .text-gray-800 {
            color: #E5E7EB;
        }
    </style>

    <script>
        // Configuración de Tailwind
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#EBF5FF',
                            100: '#E1EFFE',
                            200: '#C3DDFD',
                            300: '#A4CAFE',
                            400: '#76A9FA',
                            500: '#3F83F8',
                            600: '#1C64F2',
                            700: '#1A56DB',
                            800: '#1E429F',
                            900: '#233876',
                        },
                    }
                }
            }
        }

        // Modo oscuro
        if (localStorage.getItem('dark-mode') === 'true' ||
            (!localStorage.getItem('dark-mode') &&
             window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @livewireStyles
</head>
<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    {{ $slot }}

    <!-- Notificaciones -->
    <div id="notifications" class="fixed inset-0 flex px-4 py-6 pointer-events-none sm:p-6 items-start justify-end z-50">
        <div class="flex flex-col space-y-4 w-full max-w-sm"></div>
    </div>

    @livewireScripts

    <script>
        // Script para notificaciones
        window.addEventListener('notification', event => {
            const notificationsContainer = document.querySelector('#notifications > div');
            const notification = document.createElement('div');

            notification.className = `transform transition-all duration-300 ease-out p-4 rounded-lg shadow-lg max-w-sm w-full bg-white dark:bg-gray-800 border-l-4 ${
                event.detail.type === 'success' ? 'border-green-500' :
                event.detail.type === 'error' ? 'border-red-500' :
                event.detail.type === 'info' ? 'border-blue-500' : 'border-yellow-500'
            }`;

            notification.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        ${
                            event.detail.type === 'success' ?
                            `<svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>` :
                            event.detail.type === 'error' ?
                            `<svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>` :
                            event.detail.type === 'info' ?
                            `<svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>` :
                            `<svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>`
                        }
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">${event.detail.title || ''}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${event.detail.message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button class="inline-flex text-gray-400 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            notificationsContainer.appendChild(notification);

            // Añadir animación de entrada
            setTimeout(() => {
                notification.classList.add('translate-y-0', 'opacity-100');
                notification.classList.remove('translate-y-2', 'opacity-0');
            }, 100);

            // Configurar el cierre automático
            setTimeout(() => {
                notification.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, event.detail.timeout || 5000);

            // Configurar el cierre al hacer clic
            notification.querySelector('button').addEventListener('click', () => {
                notification.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });
        });
    </script>
</body>
</html>
