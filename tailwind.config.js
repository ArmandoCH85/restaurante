import preset from './vendor/filament/support/tailwind.config.preset'

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3C50E0',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                secondary: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7CD4FD',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e',
                    950: '#082f49',
                },
                background: '#F2F7FF',
                sidebar: '#1C2434',
                hoverSidebar: '#313D4A',
                textPrimary: '#1F2937',
                textSecondary: '#6B7280',
                border: 'rgba(0,0,0,0.06)',
            },
            screens: {
                // Monitores 16.3" específicos (14.2" x 8" - 1366x768 a 1600x900)
                'monitor-16': {'min': '1366px', 'max': '1600px'},
                // Monitores compactos de escritorio generales
                'compact-desktop': {'min': '1366px', 'max': '1920px'},
                // Monitores amplios
                'wide-desktop': {'min': '1921px'},
                // Breakpoint específico para altura limitada (típico de 16.3")
                'short-height': {'raw': '(min-width: 1366px) and (max-height: 900px)'},
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
            },
            fontSize: {
                'base': '15px',
                'nav': '14px',
            },
            spacing: {
                '18': '4.5rem',
            },
            boxShadow: {
                'tailadmin': '0 1px 2px rgba(0,0,0,0.01)',
                'tailadmin-card': '0 1px 3px rgba(0,0,0,0.04)',
                'tailadmin-modal': '0 4px 6px rgba(0,0,0,0.08)',
            },
            borderRadius: {
                'tailadmin': '8px',
            },
            transitionDuration: {
                '150': '150ms',
            },
        },
    },
    plugins: [],
};
