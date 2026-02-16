<?php

namespace App\Helpers;

use App\Services\SunatService;
use Illuminate\Support\Facades\Log;

/**
 * Helper para crear instancias de SunatService de forma segura.
 * Evita crear instancias en entornos de testing donde las migraciones pueden no estar completas.
 */
class SunatServiceHelper
{
    /**
     * Verifica si estamos en entorno de testing
     */
    private static function isTestingEnvironment(): bool
    {
        // Verificar $_ENV (usado por phpunit.xml)
        $env = $_ENV['APP_ENV'] ?? null;
        if (in_array($env, ['testing', 'test'], true)) {
            return true;
        }

        // Verificar getenv()
        $env = getenv('APP_ENV');
        if (in_array($env, ['testing', 'test'], true)) {
            return true;
        }

        // Verificar si PHPUnit está cargado
        if (class_exists(\PHPUnit\Framework\TestCase::class, false)) {
            return true;
        }

        return false;
    }

    /**
     * Crea una instancia de SunatService solo si no estamos en testing.
     *
     * @return SunatService|null
     */
    public static function createIfNotTesting(): ?SunatService
    {
        if (self::isTestingEnvironment()) {
            Log::info('SunatService no inicializado: entorno de testing detectado');
            return null;
        }

        try {
            return new SunatService();
        } catch (\Exception $e) {
            Log::error('Error al crear instancia de SunatService', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Ejecuta una acción con SunatService si no estamos en testing.
     *
     * @param callable $action La acción a ejecutar con SunatService como parámetro
     * @return mixed El resultado de la acción o null si estamos en testing
     */
    public static function runIfNotTesting(callable $action)
    {
        $sunatService = self::createIfNotTesting();
        if ($sunatService === null) {
            Log::info('Saltando ejecución de SunatService en entorno de testing');
            return null;
        }
        
        return $action($sunatService);
    }

    /**
     * Verifica si SunatService está disponible
     *
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return !self::isTestingEnvironment();
    }
}
