<?php

namespace App\Providers;

use App\Services\SunatService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Service Provider para SunatService.
 * Registra SunatService como singleton SOLO en entornos que no son testing.
 * En testing, SunatService no se registra para evitar errores de migración.
 */
class SunatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Solo registrar el singleton si NO estamos en testing
        // Verificar tanto $_ENV (usado por phpunit.xml) como getenv()
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV');
        $isTesting = in_array($env, ['testing', 'test'], true);

        if (!$isTesting) {
            $this->app->singleton(SunatService::class, function ($app) {
                try {
                    return new SunatService();
                } catch (\Exception $e) {
                    Log::error('Error al crear instancia de SunatService', [
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            });
        }
    }
}
