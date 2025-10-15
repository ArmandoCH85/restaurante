<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LIMPIEZA DE CACHÉ Y OPTIMIZACIÓN ===\n\n";

// Lista de comandos de limpieza
$commands = [
    'config:clear' => 'Limpiando caché de configuración',
    'cache:clear' => 'Limpiando caché de aplicación',
    'route:clear' => 'Limpiando caché de rutas',
    'view:clear' => 'Limpiando caché de vistas',
    'filament:clear-cached-components' => 'Limpiando componentes cacheados de Filament',
    'optimize:clear' => 'Limpiando todas las optimizaciones',
    'config:cache' => 'Regenerando caché de configuración',
    'route:cache' => 'Regenerando caché de rutas',
    'view:cache' => 'Regenerando caché de vistas',
];

foreach ($commands as $command => $description) {
    echo "$description...\n";
    
    try {
        $exitCode = $kernel->call($command);
        if ($exitCode === 0) {
            echo "✅ $description completado\n";
        } else {
            echo "⚠️  $description completado con código de salida: $exitCode\n";
        }
    } catch (Exception $e) {
        echo "❌ Error en $description: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Verificar el estado de Filament
echo "VERIFICANDO ESTADO DE FILAMENT:\n";

try {
    // Verificar si Filament está correctamente configurado
    $filamentConfig = config('filament');
    echo "✅ Configuración de Filament cargada correctamente\n";
    
    // Verificar recursos registrados
    $resources = collect($filamentConfig['resources'] ?? []);
    echo "Recursos registrados: " . $resources->count() . "\n";
    
    if ($resources->contains('App\\Filament\\Resources\\PurchaseResource')) {
        echo "✅ PurchaseResource está registrado\n";
    } else {
        echo "⚠️  PurchaseResource no encontrado en la configuración\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando Filament: " . $e->getMessage() . "\n";
}

echo "\n";

// Verificar permisos de archivos de caché
echo "VERIFICANDO PERMISOS:\n";

$cacheDirectories = [
    storage_path('framework/cache'),
    storage_path('framework/views'),
    storage_path('framework/sessions'),
    bootstrap_path('cache'),
];

foreach ($cacheDirectories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ $dir es escribible\n";
        } else {
            echo "⚠️  $dir no es escribible\n";
        }
    } else {
        echo "❌ $dir no existe\n";
    }
}

echo "\n=== LIMPIEZA COMPLETADA ===\n";
echo "Recomendaciones:\n";
echo "1. Actualiza tu navegador (Ctrl+F5 o Cmd+Shift+R)\n";
echo "2. Limpia el caché del navegador\n";
echo "3. Verifica que no haya errores de JavaScript en la consola del navegador\n";
echo "4. Si el problema persiste, revisa los logs de Laravel en storage/logs/\n";