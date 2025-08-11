<!DOCTYPE html>
<html lang="es">
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <header class="bg-white shadow dark:bg-gray-800">
        <div class="container px-4 py-4 mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ config('app.name', 'Restaurante') }}
                    </a>
                </div>

                @auth
                <div class="flex items-center space-x-4">
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
        </div>
    </header>

    <main class="py-4">
        @yield('content')
    </main>

    <footer class="py-4 mt-8 bg-white shadow dark:bg-gray-800">
        <div class="container px-4 mx-auto">
            <div class="text-sm text-center text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'Restaurante') }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</body>
</html>

