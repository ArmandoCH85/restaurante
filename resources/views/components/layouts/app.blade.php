<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Restaurante' }}</title>
    <link rel="icon" href="{{ asset('images/logoWayna.svg') }}" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/full-width-optimization.css') }}">

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @livewireStyles
</head>
<body class="antialiased bg-gray-100 text-gray-900 h-full overflow-hidden dark:bg-gray-900 dark:text-gray-100">
    {{ $slot }}

    @livewireScripts
</body>
</html>
