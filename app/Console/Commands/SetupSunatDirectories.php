<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupSunatDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:setup-directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear la estructura de directorios para certificados SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directories = [
            storage_path('app/private/sunat'),
            storage_path('app/private/sunat/certificates'),
            storage_path('app/private/sunat/certificates/beta'),
            storage_path('app/private/sunat/certificates/production'),
            storage_path('app/private/sunat/xml'),
            storage_path('app/private/sunat/xml/beta'),
            storage_path('app/private/sunat/xml/beta/signed'),
            storage_path('app/private/sunat/xml/beta/unsigned'),
            storage_path('app/private/sunat/xml/beta/cdr'),
            storage_path('app/private/sunat/xml/production'),
            storage_path('app/private/sunat/xml/production/signed'),
            storage_path('app/private/sunat/xml/production/unsigned'),
            storage_path('app/private/sunat/xml/production/cdr'),
            storage_path('app/private/sunat/pdf'),
            storage_path('app/private/sunat/pdf/beta'),
            storage_path('app/private/sunat/pdf/production'),
            storage_path('app/private/sunat/temp'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Directorio creado: {$directory}");
            } else {
                $this->comment("Directorio ya existe: {$directory}");
            }
        }

        // Crear archivo .gitignore para proteger certificados
        $gitignoreContent = "# Certificados SUNAT - No subir al repositorio\n*.p12\n*.pfx\n*.pem\n*.key\n*.crt\n";

        $gitignorePath = storage_path('app/private/sunat/.gitignore');
        if (!File::exists($gitignorePath)) {
            File::put($gitignorePath, $gitignoreContent);
            $this->info("Archivo .gitignore creado en: {$gitignorePath}");
        }

        // Crear archivo README con instrucciones
        $readmeContent = "# Directorio de Certificados SUNAT\n\n";
        $readmeContent .= "## Estructura de Directorios\n\n";
        $readmeContent .= "- `beta/`: Certificados para el entorno de pruebas de SUNAT\n";
        $readmeContent .= "- `production/`: Certificados para el entorno de producción de SUNAT\n";
        $readmeContent .= "- `temp/`: Archivos temporales\n\n";
        $readmeContent .= "## Seguridad\n\n";
        $readmeContent .= "- Los certificados están protegidos por .gitignore\n";
        $readmeContent .= "- Este directorio está fuera del directorio público\n";
        $readmeContent .= "- Solo el servidor web tiene acceso a estos archivos\n\n";
        $readmeContent .= "## Formatos Soportados\n\n";
        $readmeContent .= "- .p12 (PKCS#12)\n";
        $readmeContent .= "- .pfx (Personal Information Exchange)\n";
        $readmeContent .= "- .pem (Privacy Enhanced Mail)\n";

        $readmePath = storage_path('app/private/sunat/README.md');
        if (!File::exists($readmePath)) {
            File::put($readmePath, $readmeContent);
            $this->info("Archivo README creado en: {$readmePath}");
        }

        $this->newLine();
        $this->info('✅ Estructura de directorios SUNAT configurada correctamente');
        $this->comment('Los certificados se almacenarán de forma segura en storage/app/private/sunat/');

        return Command::SUCCESS;
    }
}
