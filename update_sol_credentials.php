<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar el entorno de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;

echo "=== Actualizando credenciales SOL en app_settings ===\n\n";

// Obtener valores del .env
$envSolUser = env('GREENTER_SOL_USER', 'IERCEST1');
$envSolPass = env('GREENTER_SOL_PASS', 'Qrico123');

echo "Valores del .env:\n";
echo "GREENTER_SOL_USER: {$envSolUser}\n";
echo "GREENTER_SOL_PASS: {$envSolPass}\n\n";

// Obtener valores actuales de la base de datos
$currentUser = AppSetting::getSetting('FacturacionElectronica', 'sol_user');
$currentPass = AppSetting::getDecryptedSetting('FacturacionElectronica', 'sol_password');

echo "Valores actuales en app_settings:\n";
echo "sol_user: {$currentUser}\n";
echo "sol_password: {$currentPass}\n\n";

// Actualizar si son diferentes
if ($currentUser !== $envSolUser) {
    echo "Actualizando sol_user de '{$currentUser}' a '{$envSolUser}'...\n";
    AppSetting::where('tab', 'FacturacionElectronica')
              ->where('key', 'sol_user')
              ->update(['value' => $envSolUser]);
    echo "✓ sol_user actualizado\n";
} else {
    echo "sol_user ya está correcto\n";
}

if ($currentPass !== $envSolPass) {
    echo "Actualizando sol_password de '{$currentPass}' a '{$envSolPass}'...\n";
    AppSetting::setEncryptedSetting('FacturacionElectronica', 'sol_password', $envSolPass);
    echo "✓ sol_password actualizado y cifrado\n";
} else {
    echo "sol_password ya está correcto\n";
}

echo "\n=== Verificando cambios ===\n";

// Verificar los cambios
$newUser = AppSetting::getSetting('FacturacionElectronica', 'sol_user');
$newPass = AppSetting::getDecryptedSetting('FacturacionElectronica', 'sol_password');

echo "Nuevos valores en app_settings:\n";
echo "sol_user: {$newUser}\n";
echo "sol_password: {$newPass}\n";

if ($newUser === $envSolUser && $newPass === $envSolPass) {
    echo "\n✅ Credenciales SOL actualizadas correctamente\n";
} else {
    echo "\n❌ Error al actualizar las credenciales\n";
}

echo "\n=== Proceso completado ===\n";