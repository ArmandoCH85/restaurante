<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;
use Illuminate\Support\Facades\File;

class CheckSunatConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:check-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar la configuración actual de SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando configuración de SUNAT...');
        $this->newLine();

        // 1. Verificar entorno
        $environment = AppSetting::getSetting('FacturacionElectronica', 'environment');
        $this->info("1. Entorno actual: " . ($environment ?: 'No configurado'));
        if ($environment === 'production') {
            $this->line('   🟢 Configurado para PRODUCCIÓN');
        } elseif ($environment === 'beta') {
            $this->line('   🔵 Configurado para BETA/PRUEBAS');
        } else {
            $this->error('   ❌ Entorno no configurado');
        }
        $this->newLine();

        // 2. Verificar certificado
        $certificatePath = AppSetting::getSetting('FacturacionElectronica', 'certificate_path');
        $this->info("2. Certificado digital:");
        if ($certificatePath) {
            $this->line("   📁 Ruta: {$certificatePath}");
            if (File::exists($certificatePath)) {
                $size = round(File::size($certificatePath) / 1024, 2);
                $extension = strtolower(pathinfo($certificatePath, PATHINFO_EXTENSION));
                $this->line("   ✅ Archivo existe ({$size} KB, .{$extension})");
            } else {
                $this->error("   ❌ Archivo NO existe en la ruta especificada");
            }
        } else {
            $this->error("   ❌ Ruta del certificado no configurada");
        }
        $this->newLine();

        // 3. Verificar contraseña del certificado
        $certificatePassword = AppSetting::getDecryptedSetting('FacturacionElectronica', 'certificate_password');
        $this->info("3. Contraseña del certificado:");
        if ($certificatePassword) {
            $this->line("   ✅ Configurada (longitud: " . strlen($certificatePassword) . " caracteres)");
        } else {
            $this->error("   ❌ No configurada");
        }
        $this->newLine();

        // 4. Verificar credenciales SOL
        $solUser = AppSetting::getSetting('FacturacionElectronica', 'sol_user');
        $solPassword = AppSetting::getDecryptedSetting('FacturacionElectronica', 'sol_password');
        
        $this->info("4. Credenciales SOL:");
        if ($solUser) {
            $this->line("   👤 Usuario: {$solUser}");
        } else {
            $this->error("   ❌ Usuario SOL no configurado");
        }
        
        if ($solPassword) {
            $this->line("   🔑 Contraseña: Configurada (longitud: " . strlen($solPassword) . " caracteres)");
        } else {
            $this->error("   ❌ Contraseña SOL no configurada");
        }
        $this->newLine();

        // 5. Verificar directorios
        $this->info("5. Directorios de almacenamiento:");
        $directories = [
            'Beta' => storage_path('app/private/sunat/certificates/beta'),
            'Producción' => storage_path('app/private/sunat/certificates/production'),
        ];

        foreach ($directories as $name => $path) {
            if (File::exists($path)) {
                $files = File::files($path);
                $certFiles = array_filter($files, function($file) {
                    return in_array(strtolower($file->getExtension()), ['pfx', 'p12', 'pem']);
                });
                $this->line("   📁 {$name}: ✅ Existe (" . count($certFiles) . " certificados)");
            } else {
                $this->line("   📁 {$name}: ❌ No existe");
            }
        }
        $this->newLine();

        // 6. Resumen y recomendaciones
        $this->info("📋 RESUMEN:");
        
        $issues = [];
        if (!$environment) $issues[] = "Entorno no configurado";
        if (!$certificatePath || !File::exists($certificatePath)) $issues[] = "Certificado no encontrado";
        if (!$certificatePassword) $issues[] = "Contraseña del certificado no configurada";
        if (!$solUser) $issues[] = "Usuario SOL no configurado";
        if (!$solPassword) $issues[] = "Contraseña SOL no configurada";

        if (empty($issues)) {
            $this->line("   ✅ Configuración completa");
            $this->info("   🎉 ¡Todo está configurado correctamente!");
        } else {
            $this->error("   ❌ Problemas encontrados:");
            foreach ($issues as $issue) {
                $this->line("      • {$issue}");
            }
            $this->newLine();
            $this->warn("💡 RECOMENDACIONES:");
            $this->line("   1. Ve al panel de administración → Configuración de Facturación Electrónica");
            $this->line("   2. Sube tu certificado de producción (.pfx/.p12)");
            $this->line("   3. Configura las credenciales SOL de producción");
            $this->line("   4. Asegúrate de que el entorno esté en 'Producción'");
        }

        return 0;
    }
}
