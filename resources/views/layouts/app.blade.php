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
    <header class="bg-white sticky top-0 z-50 border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700 shadow-xs">
        <div class="container px-4 py-3 mx-auto">
            <div class="flex items-center justify-between h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a href="{{ url('/') }}" class="flex items-center transition-opacity hover:opacity-80" aria-label="Inicio">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Waynasoft" class="h-10 w-10 object-contain">
                    </a>
                    <div class="hidden sm:flex flex-col gap-0.5">
                        <span class="text-sm font-bold text-gray-900 dark:text-white leading-none">WAYNA</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Restaurant</span>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-8 flex-1 px-8">
                    <a href="#herramientas" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200 relative group">
                        Soluciones
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#mas-info" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200 relative group">
                        Beneficios
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#demo" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200 relative group">
                        Contacto
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </nav>

                <!-- Right side: CTA and Auth -->
                <div class="flex items-center gap-4">
                    <!-- CTA Button -->
                    <a href="#demo" class="hidden md:inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold hover:shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Demo
                    </a>

                    <!-- User Menu (Auth) -->
                    @auth
                    <div class="hidden md:flex items-center gap-4 pl-4 border-l border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">{{ Auth::user()->name }}</span>
                        <button id="user-menu-toggle" class="relative inline-flex items-center justify-center w-9 h-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="user-dropdown" class="hidden absolute top-full right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                            <a href="{{ route('tables.map') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-t-lg transition-colors">
                                Mis Pedidos
                            </a>
                            <form method="POST" action="{{ auth()->user()?->hasRole('waiter') ? route('filament.waiter.auth.logout') : route('filament.admin.auth.logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-b-lg transition-colors">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-controls="mobile-menu" aria-expanded="false" aria-label="Abrir menú">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu panel -->
            <div id="mobile-menu" class="md:hidden hidden pt-4 pb-4 border-t border-gray-200 dark:border-gray-700">
                <nav class="flex flex-col space-y-1">
                    <a href="#herramientas" class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Soluciones</a>
                    <a href="#mas-info" class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Beneficios</a>
                    <a href="#demo" class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Contacto</a>
                    <div class="px-4 py-3">
                        <a href="#demo" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold hover:shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Solicitar Demo
                        </a>
                    </div>
                    @auth
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 space-y-1">
                        <a href="{{ route('tables.map') }}" class="block px-4 py-3 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Mis Pedidos
                        </a>
                        <form method="POST" action="{{ auth()->user()?->hasRole('waiter') ? route('filament.waiter.auth.logout') : route('filament.admin.auth.logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                    @endauth
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
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        mobileMenuBtn.setAttribute('aria-expanded', 'true');
                    } else {
                        mobileMenu.classList.add('hidden');
                        mobileMenuBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                mobileMenu.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        mobileMenuBtn.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            // User dropdown menu
            const userMenuToggle = document.getElementById('user-menu-toggle');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
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

