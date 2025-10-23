<!DOCTYPE html>
<html lang="es" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Restaurante') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos personalizados -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
        }

        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .notification.info {
            background-color: #3b82f6;
            color: white;
        }

        .notification.success {
            background-color: #10b981;
            color: white;
        }

        .notification.error {
            background-color: #ef4444;
            color: white;
        }

        .notification.warning {
            background-color: #f59e0b;
            color: white;
        }
    </style>
    <!-- Scripts de la aplicación -->
    <!-- CSS y JS en duro, sin Vite -->
    @stack('styles')
</head>
<body class="min-h-screen dark:bg-gray-900" style="background: linear-gradient(180deg, #f7fbff 0%, #f2f6fc 100%);">
    <header class="bg-white sticky top-0 z-50 shadow-sm dark:bg-gray-800">
        <div class="container px-4 py-6 md:py-5 sm:py-4 mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center" aria-label="Inicio">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Waynasoft" class="h-[120px] md:h-32 sm:h-20 w-auto">
                    </a>
                    <span class="ml-4 text-sm md:text-base text-gray-600 dark:text-gray-300 font-bold hidden sm:inline">Software de gestión para restaurantes</span>
                </div>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="#herramientas" class="font-semibold text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Soluciones</a>
                    <a href="#mas-info" class="font-semibold text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Beneficios</a>
                    <a href="#demo" class="font-semibold text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Contacto</a>
                    <a href="#demo" class="ml-2 inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-sm transition">Solicita demo</a>
                </nav>

                <!-- Mobile hamburger -->
                <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-controls="mobile-menu" aria-expanded="false" aria-label="Abrir menú">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                @auth
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-gray-600 dark:text-gray-300">{{ Auth::user()->name }}</span>
                    <a href="{{ route('tables.map') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        Mis Pedidos
                    </a>
                    <form method="POST" action="{{ auth()->user()?->hasRole('waiter') ? route('filament.waiter.auth.logout') : route('filament.admin.auth.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
                @endauth
            </div>

            <!-- Mobile menu panel -->
            <div id="mobile-menu" class="md:hidden hidden mt-4">
                <nav class="flex flex-col space-y-2">
                    <a href="#herramientas" class="py-2 px-3 rounded font-semibold text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-700">Soluciones</a>
                    <a href="#mas-info" class="py-2 px-3 rounded font-semibold text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-700">Beneficios</a>
                    <a href="#demo" class="py-2 px-3 rounded font-semibold text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-700">Contacto</a>
                    <a href="#demo" class="py-2 px-3 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-sm transition">Solicita demo</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="py-4">
        @yield('content')
    </main>

    @stack('scripts')

    <script>
        (function() {
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', () => {
                    const isHidden = menu.classList.contains('hidden');
                    if (isHidden) {
                        menu.classList.remove('hidden');
                        btn.setAttribute('aria-expanded', 'true');
                    } else {
                        menu.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                    }
                });

                // Cerrar menú al hacer clic en cualquier enlace del menú móvil
                menu.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        menu.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                    });
                });
            }
        })();
    </script>

    <footer class="py-4 mt-8 bg-white shadow dark:bg-gray-800">
        <div class="container px-4 mx-auto">
            <div class="text-sm text-center text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} Waynasoft. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</body>
</html>

