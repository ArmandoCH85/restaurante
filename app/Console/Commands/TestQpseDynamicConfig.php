<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QpsService;
use App\Models\AppSetting;
use Exception;

class TestQpseDynamicConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qpse:test-config {--show-credentials : Mostrar credenciales en el output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la configuración dinámica de QPSE y validar conectividad';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Probando configuración dinámica de QPSE...');
        $this->newLine();

        // 1. Verificar configuración de entorno
        $this->info('📋 Verificando configuración de entorno:');
        $isProduction = AppSetting::getSetting('FacturacionElectronica', 'sunat_production') === '1';
        $environment = $isProduction ? 'production' : 'beta';
        
        $this->line("   Entorno actual: <fg=yellow>{$environment}</>");
        
        // 2. Verificar endpoints configurados
        $this->info('🌐 Verificando endpoints configurados:');
        $betaEndpoint = AppSetting::getQpseEndpointBetaFromFacturacion();
        $productionEndpoint = AppSetting::getQpseEndpointProductionFromFacturacion();
        
        $this->line("   Endpoint Beta: <fg=cyan>" . ($betaEndpoint ?: 'NO CONFIGURADO') . "</>");
        $this->line("   Endpoint Producción: <fg=cyan>" . ($productionEndpoint ?: 'NO CONFIGURADO') . "</>");
        
        $currentEndpoint = AppSetting::getQpseEndpointByEnvironmentFromFacturacion($environment);
        $this->line("   Endpoint actual ({$environment}): <fg=green>" . ($currentEndpoint ?: 'NO CONFIGURADO') . "</>");
        
        // 3. Verificar credenciales
        $this->info('🔐 Verificando credenciales:');
        $credentials = AppSetting::getQpseCredentialsFromFacturacion();
        
        if ($this->option('show-credentials')) {
            $this->line("   Usuario: <fg=yellow>" . ($credentials['username'] ?: 'NO CONFIGURADO') . "</>");
            $this->line("   Contraseña: <fg=yellow>" . ($credentials['password'] ? '***CONFIGURADA***' : 'NO CONFIGURADA') . "</>");
        } else {
            $this->line("   Usuario: " . ($credentials['username'] ? '<fg=green>✓ Configurado</>' : '<fg=red>✗ No configurado</>')); 
            $this->line("   Contraseña: " . ($credentials['password'] ? '<fg=green>✓ Configurada</>' : '<fg=red>✗ No configurada</>')); 
        }
        
        // 4. Verificar configuración de servicios
        $this->info('⚙️ Verificando configuración de servicios:');
        $useDynamicConfig = config('services.qps.use_dynamic_config', true);
        $this->line("   Configuración dinámica: " . ($useDynamicConfig ? '<fg=green>✓ Habilitada</>' : '<fg=yellow>✗ Deshabilitada</>')); 
        
        if (!$useDynamicConfig) {
            $this->warn('   ⚠️  La configuración dinámica está deshabilitada. Se usará config/services.php');
        }
        
        $this->newLine();
        
        // 5. Probar conectividad con QPS
        if (!$currentEndpoint) {
            $this->error('❌ No se puede probar conectividad: endpoint no configurado para el entorno actual.');
            return 1;
        }
        
        if (!$credentials['username'] || !$credentials['password']) {
            $this->error('❌ No se puede probar conectividad: credenciales no configuradas.');
            return 1;
        }
        
        $this->info('🔌 Probando conectividad con QPSE...');
        
        try {
            $qpsService = new QpsService();
            $token = $qpsService->getAccessToken();
            
            $this->line('<fg=green>✅ Conectividad exitosa!</>');
            $this->line("   Token obtenido: " . substr($token, 0, 20) . '...');
            
        } catch (Exception $e) {
            $this->error('❌ Error de conectividad:');
            $this->line('   ' . $e->getMessage());
            
            // Sugerencias de solución
            $this->newLine();
            $this->info('💡 Sugerencias de solución:');
            
            if (str_contains($e->getMessage(), 'Credenciales inválidas')) {
                $this->line('   • Verifique que el usuario y contraseña QPSE sean correctos');
                $this->line('   • Asegúrese de que la cuenta esté activa en QPSE');
            } elseif (str_contains($e->getMessage(), 'conexión')) {
                $this->line('   • Verifique su conexión a internet');
                $this->line('   • Confirme que la URL del endpoint sea correcta');
                $this->line('   • Verifique que no haya firewall bloqueando la conexión');
            } elseif (str_contains($e->getMessage(), 'Endpoint no encontrado')) {
                $this->line('   • Verifique que la URL del endpoint sea correcta');
                $this->line('   • Confirme que esté usando el endpoint correcto para su entorno');
            }
            
            return 1;
        }
        
        $this->newLine();
        $this->info('🎉 Configuración QPSE validada exitosamente!');
        
        return 0;
    }
}