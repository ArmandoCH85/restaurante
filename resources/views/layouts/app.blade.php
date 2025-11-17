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
    <header class="bg-white sticky top-0 z-50" style="background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%); border-bottom: 1px solid rgba(226, 232, 240, 0.6); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo y Brand Premium -->
                <div class="flex items-center flex-shrink-0">
                    <a href="{{ url('/') }}" class="flex items-center transition-all duration-300 hover:opacity-85 group" aria-label="Inicio" style="gap: 12px;">
                        <!-- Logo Container -->
                        <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);" class="group-hover:shadow-lg group-hover:scale-105">
                            <img src="{{ asset('images/logo.jpg') }}" alt="Wayna" style="width: 38px; height: 38px; border-radius: 8px; object-fit: contain;">
                        </div>
                        <!-- Brand Text -->
                        <div style="display: flex; flex-direction: column; gap: 1px;">
                            <span style="font-size: 16px; font-weight: 900; color: #0f172a; letter-spacing: -0.4px;">WAYNA</span>
                            <span style="font-size: 11px; color: #94a3b8; font-weight: 600; letter-spacing: 0.5px;">SOFTWARE</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Premium -->
                <nav class="hidden lg:flex items-center" style="gap: 4px;">
                    <a href="#herramientas" style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); position: relative; letter-spacing: 0.3px; text-transform: uppercase;" class="hover:text-#0f172a group" onmouseover="this.style.color='#0f172a'; this.style.background='rgba(59, 130, 246, 0.08)';" onmouseout="this.style.color='#475569'; this.style.background='transparent';">
                        Características
                        <span style="position: absolute; bottom: 8px; left: 18px; width: 0; height: 2px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); transition: all 0.3s ease; border-radius: 1px;" class="group-hover:w-[calc(100%-36px)]"></span>
                    </a>
                    <a href="#mas-info" style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); position: relative; letter-spacing: 0.3px; text-transform: uppercase;" class="hover:text-#0f172a group" onmouseover="this.style.color='#0f172a'; this.style.background='rgba(59, 130, 246, 0.08)';" onmouseout="this.style.color='#475569'; this.style.background='transparent';">
                        Beneficios
                        <span style="position: absolute; bottom: 8px; left: 18px; width: 0; height: 2px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); transition: all 0.3s ease; border-radius: 1px;" class="group-hover:w-[calc(100%-36px)]"></span>
                    </a>
                    <a href="#testimonios" style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); position: relative; letter-spacing: 0.3px; text-transform: uppercase;" class="hover:text-#0f172a group" onmouseover="this.style.color='#0f172a'; this.style.background='rgba(59, 130, 246, 0.08)';" onmouseout="this.style.color='#475569'; this.style.background='transparent';">
                        Casos de Éxito
                        <span style="position: absolute; bottom: 8px; left: 18px; width: 0; height: 2px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); transition: all 0.3s ease; border-radius: 1px;" class="group-hover:w-[calc(100%-36px)]"></span>
                    </a>
                    <a href="#pasos" style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); position: relative; letter-spacing: 0.3px; text-transform: uppercase;" class="hover:text-#0f172a group" onmouseover="this.style.color='#0f172a'; this.style.background='rgba(59, 130, 246, 0.08)';" onmouseout="this.style.color='#475569'; this.style.background='transparent';">
                        Cómo Empezar
                        <span style="position: absolute; bottom: 8px; left: 18px; width: 0; height: 2px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); transition: all 0.3s ease; border-radius: 1px;" class="group-hover:w-[calc(100%-36px)]"></span>
                    </a>
                    <a href="#demo" style="padding: 10px 18px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); position: relative; letter-spacing: 0.3px; text-transform: uppercase;" class="hover:text-#0f172a group" onmouseover="this.style.color='#0f172a'; this.style.background='rgba(59, 130, 246, 0.08)';" onmouseout="this.style.color='#475569'; this.style.background='transparent';">
                        Demo
                        <span style="position: absolute; bottom: 8px; left: 18px; width: 0; height: 2px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); transition: all 0.3s ease; border-radius: 1px;" class="group-hover:w-[calc(100%-36px)]"></span>
                    </a>
                </nav>

                <!-- Right side: CTA and Auth -->
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- CTA Button Premium -->
                    <a href="#demo" style="padding: 12px 28px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; text-decoration: none; font-size: 13px; font-weight: 700; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(59, 130, 246, 0.3); cursor: pointer; text-transform: uppercase; letter-spacing: 0.3px; display: none;" class="lg:inline-flex lg:items-center lg:gap-2" onmouseover="this.style.boxShadow='0 16px 40px rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='0 10px 28px rgba(59, 130, 246, 0.3)'; this.style.transform='translateY(0)'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="display: inline;">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        Solicitar Demo
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

                    <!-- Mobile Menu Button Premium -->
                    <button id="mobile-menu-button" style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border: 1px solid rgba(59, 130, 246, 0.2); cursor: pointer; transition: all 0.3s ease; color: #3b82f6;" class="lg:hidden" onmouseover="this.style.background='rgba(59, 130, 246, 0.15)'; this.style.borderColor='rgba(59, 130, 246, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%)'; this.style.borderColor='rgba(59, 130, 246, 0.2)';" aria-label="Abrir menú">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu panel -->
            <div id="mobile-menu" class="lg:hidden hidden" style="padding: 20px 0; border-top: 1px solid rgba(226, 232, 240, 0.6); background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);">
                <nav style="display: flex; flex-direction: column; gap: 6px;">
                    <a href="#herramientas" style="padding: 12px 20px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">Características</a>
                    <a href="#mas-info" style="padding: 12px 20px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">Beneficios</a>
                    <a href="#testimonios" style="padding: 12px 20px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">Casos de Éxito</a>
                    <a href="#pasos" style="padding: 12px 20px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">Cómo Empezar</a>
                    <a href="#demo" style="padding: 12px 20px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">Demo</a>
                    <div style="padding: 16px 20px; margin-top: 12px; border-top: 1px solid rgba(226, 232, 240, 0.6); padding-top: 20px;">
                        <a href="#demo" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px 20px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; text-decoration: none; font-size: 13px; font-weight: 700; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.boxShadow='0 12px 24px rgba(59, 130, 246, 0.3)';" onmouseout="this.style.boxShadow='0 8px 16px rgba(59, 130, 246, 0.15)';">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Solicitar Demo
                        </a>
                    </div>
                    @auth
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(226, 232, 240, 0.6);">
                        <a href="{{ route('tables.map') }}" style="display: block; padding: 12px 20px; font-size: 13px; font-weight: 700; color: #475569; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">&#x1F4CB; Mis Pedidos</a>
                        <form method="POST" action="{{ auth()->user()?->hasRole('waiter') ? route('filament.waiter.auth.logout') : route('filament.admin.auth.logout') }}" style="margin-top: 8px;">
                            @csrf
                            <button type="submit" style="width: 100%; text-align: left; padding: 12px 20px; font-size: 13px; font-weight: 700; color: #ef4444; background: none; border: none; cursor: pointer; border-radius: 10px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.3px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)';" onmouseout="this.style.background='transparent';">&#x1F6AA; Cerrar Sesión</button>
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

