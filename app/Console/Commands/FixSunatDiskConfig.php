<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FixSunatDiskConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:fix-disk-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Solucionar problemas de configuración del disco certificates para SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Solucionando problemas de configuración del disco certificates...');
        $this->newLine();

        // Paso 1: Limpiar cache de configuración
        $this->info('1. Limpiando cache de configuración...');
        $this->call('config:clear');
        $this->info('   ✅ Cache de configuración limpiado');

        // Paso 2: Limpiar cache de rutas
        $this->info('2. Limpiando cache de rutas...');
        $this->call('route:clear');
        $this->info('   ✅ Cache de rutas limpiado');

        // Paso 3: Limpiar cache de vistas
        $this->info('3. Limpiando cache de vistas...');
        $this->call('view:clear');
        $this->info('   ✅ Cache de vistas limpiado');

        // Paso 4: Verificar que los directorios existan
        $this->info('4. Verificando directorios...');
        $directories = [
            storage_path('app/private'),
            storage_path('app/private/sunat'),
            storage_path('app/private/sunat/certificates'),
            storage_path('app/private/sunat/certificates/beta'),
            storage_path('app/private/sunat/certificates/production'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("   📁 Directorio creado: {$directory}");
            } else {
                $this->comment("   📁 Directorio existe: {$directory}");
            }
        }

        // Paso 5: Verificar configuración del disco
        $this->info('5. Verificando configuración del disco certificates...');
        try {
            $disk = Storage::disk('certificates');
            $this->info('   ✅ Disco "certificates" configurado correctamente');
            
            // Probar escribir un archivo de prueba
            $testFile = 'test_' . time() . '.txt';
            $disk->put($testFile, 'Archivo de prueba para verificar funcionamiento');
            
            if ($disk->exists($testFile)) {
                $this->info('   ✅ Prueba de escritura exitosa');
                $disk->delete($testFile);
                $this->info('   ✅ Archivo de prueba eliminado');
            } else {
                $this->error('   ❌ Error en prueba de escritura');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error con el disco "certificates": ' . $e->getMessage());
            
            // Paso 6: Regenerar cache de configuración
            $this->info('6. Regenerando cache de configuración...');
            $this->call('config:cache');
            $this->info('   ✅ Cache de configuración regenerado');
            
            // Intentar nuevamente
            try {
                $disk = Storage::disk('certificates');
                $this->info('   ✅ Disco "certificates" funcionando después de regenerar cache');
            } catch (\Exception $e) {
                $this->error('   ❌ Error persistente: ' . $e->getMessage());
                $this->newLine();
                $this->error('🚨 SOLUCIÓN MANUAL REQUERIDA:');
                $this->line('1. Ejecuta en el servidor: php artisan config:clear');
                $this->line('2. Ejecuta en el servidor: php artisan config:cache');
                $this->line('3. Reinicia el servidor web (nginx/apache)');
                $this->line('4. Verifica que el archivo config/filesystems.php contenga la configuración del disco "certificates"');
                return 1;
            }
        }

        // Paso 7: Verificar permisos
        $this->info('7. Verificando permisos de directorios...');
        $certificatesPath = storage_path('app/private/sunat/certificates');
        if (is_writable($certificatesPath)) {
            $this->info('   ✅ Permisos de escritura correctos');
        } else {
            $this->warn('   ⚠️  Verificar permisos de escritura en: ' . $certificatesPath);
        }

        $this->newLine();
        $this->info('🎉 Configuración del disco certificates solucionada exitosamente');
        $this->comment('Ahora puedes subir certificados desde la interfaz de Filament');
        
        return 0;
    }
}
