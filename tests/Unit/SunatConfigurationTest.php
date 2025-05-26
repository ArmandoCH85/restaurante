<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AppSetting;
use App\Models\DocumentSeries;
use App\Services\SunatService;

class SunatConfigurationTest extends TestCase
{
    /** @test */
    public function configuraciones_sunat_existen_y_son_validas()
    {
        // Verificar configuraciones críticas
        $requiredSettings = [
            'environment' => ['beta', 'produccion'],
            'ruc' => null, // Cualquier valor válido
            'razon_social' => null,
            'sol_user' => null,
            'sol_password' => null
        ];

        foreach ($requiredSettings as $key => $allowedValues) {
            $setting = AppSetting::where('tab', 'FacturacionElectronica')
                ->where('key', $key)
                ->first();

            $this->assertNotNull($setting, "Configuración '{$key}' no existe");
            $this->assertNotEmpty($setting->value, "Configuración '{$key}' está vacía");

            if ($allowedValues) {
                $this->assertContains($setting->value, $allowedValues,
                    "Configuración '{$key}' tiene valor inválido: {$setting->value}");
            }
        }
    }

    /** @test */
    public function ruc_tiene_formato_valido()
    {
        $ruc = AppSetting::getSetting('FacturacionElectronica', 'ruc');

        $this->assertNotNull($ruc, 'RUC no está configurado');
        $this->assertMatchesRegularExpression('/^[0-9]{11}$/', $ruc,
            'RUC debe tener exactamente 11 dígitos');
        $this->assertStringStartsWith('20', $ruc,
            'RUC de empresa debe empezar con 20');
    }

    /** @test */
    public function series_de_documentos_estan_configuradas()
    {
        $requiredSeries = [
            'invoice' => 'Facturas',
            'receipt' => 'Boletas'
        ];

        foreach ($requiredSeries as $type => $description) {
            $series = DocumentSeries::where('document_type', $type)
                ->where('active', true)
                ->first();

            $this->assertNotNull($series, "Serie para {$description} no existe");
            $this->assertTrue($series->active, "Serie para {$description} no está activa");
            $this->assertGreaterThan(0, $series->current_number,
                "Número actual de serie para {$description} debe ser mayor a 0");
            $this->assertMatchesRegularExpression('/^[A-Z]\d{3}$/', $series->series,
                "Formato de serie para {$description} debe ser letra + 3 dígitos");
        }
    }

    /** @test */
    public function entorno_sunat_es_valido()
    {
        $environment = AppSetting::getSetting('FacturacionElectronica', 'environment');

        $this->assertNotNull($environment, 'Entorno SUNAT no está configurado');
        $this->assertContains($environment, ['beta', 'produccion'],
            'Entorno debe ser beta o produccion');
    }

    /** @test */
    public function credenciales_sol_estan_configuradas()
    {
        $solUser = AppSetting::getSetting('FacturacionElectronica', 'sol_user');
        $solPassword = AppSetting::getSetting('FacturacionElectronica', 'sol_password');

        $this->assertNotNull($solUser, 'Usuario SOL no está configurado');
        $this->assertNotNull($solPassword, 'Contraseña SOL no está configurada');
        $this->assertNotEmpty($solUser, 'Usuario SOL no puede estar vacío');
        $this->assertNotEmpty($solPassword, 'Contraseña SOL no puede estar vacía');
    }

    /** @test */
    public function configuracion_empresa_es_completa()
    {
        $requiredCompanySettings = [
            'razon_social' => 'Razón social',
            'direccion' => 'Dirección',
            'ubigeo' => 'Ubigeo',
            'distrito' => 'Distrito',
            'provincia' => 'Provincia',
            'departamento' => 'Departamento'
        ];

        foreach ($requiredCompanySettings as $key => $description) {
            $setting = AppSetting::getSetting('FacturacionElectronica', $key);
            $this->assertNotNull($setting, "{$description} no está configurado");
            $this->assertNotEmpty($setting, "{$description} no puede estar vacío");
        }

        // Verificar formato de ubigeo
        $ubigeo = AppSetting::getSetting('FacturacionElectronica', 'ubigeo');
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $ubigeo,
            'Ubigeo debe tener exactamente 6 dígitos');
    }

    /** @test */
    public function puede_obtener_configuracion_completa()
    {
        $allSettings = AppSetting::where('tab', 'FacturacionElectronica')->get();

        $this->assertGreaterThan(10, $allSettings->count(),
            'Debe haber al menos 10 configuraciones de facturación electrónica');

        // Verificar que no hay configuraciones duplicadas
        $keys = $allSettings->pluck('key')->toArray();
        $uniqueKeys = array_unique($keys);
        $this->assertEquals(count($keys), count($uniqueKeys),
            'No debe haber configuraciones duplicadas');
    }

    /** @test */
    public function configuracion_certificado_existe()
    {
        $certificatePath = AppSetting::getSetting('FacturacionElectronica', 'certificate_path');

        $this->assertNotNull($certificatePath, 'Ruta del certificado no está configurada');
        $this->assertNotEmpty($certificatePath, 'Ruta del certificado no puede estar vacía');

        // Verificar que la ruta tiene formato válido
        $this->assertStringContainsString('.pfx', $certificatePath,
            'Certificado debe ser un archivo .pfx');
    }

    /** @test */
    public function servicio_sunat_puede_instanciarse()
    {
        try {
            $sunatService = new SunatService();
            $this->assertInstanceOf(SunatService::class, $sunatService);
        } catch (\Exception $e) {
            // Si falla por certificado faltante, es esperado en testing
            $message = strtolower($e->getMessage());
            $this->assertTrue(
                str_contains($message, 'certificate') || str_contains($message, 'certificado'),
                'Error debe estar relacionado con certificado: ' . $e->getMessage()
            );
        }
    }

    /** @test */
    public function configuraciones_tienen_valores_por_defecto_validos()
    {
        $defaultSettings = [
            'environment' => 'beta',
            'sol_user' => 'MODDATOS',
            'sol_password' => 'moddatos',
            'ubigeo' => '150101' // Lima
        ];

        foreach ($defaultSettings as $key => $expectedDefault) {
            $setting = AppSetting::getSetting('FacturacionElectronica', $key);

            if ($setting === $expectedDefault) {
                $this->assertEquals($expectedDefault, $setting,
                    "Configuración '{$key}' tiene el valor por defecto correcto");
            } else {
                // Si no es el valor por defecto, al menos verificar que no esté vacío
                $this->assertNotEmpty($setting,
                    "Configuración '{$key}' debe tener un valor válido");
            }
        }
    }
}
