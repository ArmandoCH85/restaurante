<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuración de la Empresa (Tab: Empresa)
        $empresaSettings = [
            [
                'tab' => 'Empresa',
                'key' => 'ruc',
                'default' => '20123456789',
                'value' => '20123456789', // RUC de ejemplo
            ],
            [
                'tab' => 'Empresa',
                'key' => 'razon_social',
                'default' => 'MI EMPRESA EJEMPLO S.A.C.',
                'value' => 'MI EMPRESA EJEMPLO S.A.C.', // Razón social de ejemplo
            ],
            [
                'tab' => 'Empresa',
                'key' => 'nombre_comercial',
                'default' => 'El Sabor Ejemplo',
                'value' => 'El Sabor Ejemplo', // Nombre comercial de ejemplo
            ],
            [
                'tab' => 'Empresa',
                'key' => 'direccion',
                'default' => 'Av. Las Palmeras 555, Urb. Ejemplo, Los Olivos',
                'value' => 'Av. Las Palmeras 555, Urb. Ejemplo, Los Olivos', // Dirección de ejemplo
            ],
            [
                'tab' => 'Empresa',
                'key' => 'ubigeo',
                'default' => '150117',
                'value' => '150117', // Código Ubigeo para Los Olivos, Lima
            ],
            [
                'tab' => 'Empresa',
                'key' => 'distrito',
                'default' => 'LOS OLIVOS',
                'value' => 'LOS OLIVOS',
            ],
            [
                'tab' => 'Empresa',
                'key' => 'provincia',
                'default' => 'LIMA',
                'value' => 'LIMA',
            ],
            [
                'tab' => 'Empresa',
                'key' => 'departamento',
                'default' => 'LIMA',
                'value' => 'LIMA',
            ],
            [
                'tab' => 'Empresa',
                'key' => 'codigo_pais',
                'default' => 'PE',
                'value' => 'PE',
            ],
            [
                'tab' => 'Empresa',
                'key' => 'telefono',
                'default' => '017175500',
                'value' => '017175500', // Teléfono de ejemplo
            ],
            [
                'tab' => 'Empresa',
                'key' => 'email',
                'default' => 'facturacion@miempresa-ejemplo.com.pe',
                'value' => 'facturacion@miempresa-ejemplo.com.pe', // Email de ejemplo
            ],
            [
                'tab' => 'Empresa',
                'key' => 'factiliza_token',
                'default' => '',
                'value' => '', // Token de la API de Factiliza para búsqueda de RUC
            ],
        ];

        // Configuración de Facturación Electrónica (Tab: FacturacionElectronica)
        $facturacionSettings = [
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'soap_type',
                'default' => 'sunat',
                'value' => 'sunat', // Podría ser 'ose' si usas un Operador de Servicios Electrónicos
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'environment',
                'default' => 'beta',
                'value' => 'beta', // Entorno de pruebas SUNAT ('beta' o 'homologacion'). Cambiar a 'produccion' para real.
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'sol_user',
                'default' => 'MODDATOS',
                'value' => 'MODDATOS', // Usuario secundario SOL (este es común para el entorno Beta de SUNAT)
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'sol_password',
                'default' => Crypt::encryptString('moddatos'), // Valor cifrado para entorno de pruebas
                'value' => Crypt::encryptString('moddatos'), // ¡IMPORTANTE! Este valor DEBE estar CIFRADO
                // NOTA: En producción, usar Crypt::encryptString('tu_clave_real')
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'certificate_path',
                'default' => '',
                'value' => '', // Se actualizará automáticamente al subir certificado
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'certificate_password',
                'default' => Crypt::encryptString('123456'), // Valor cifrado para entorno de pruebas
                'value' => Crypt::encryptString('123456'), // ¡IMPORTANTE! Este valor DEBE estar CIFRADO
                // NOTA: En producción, usar Crypt::encryptString('clave_certificado_real')
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'send_automatically',
                'default' => 'false',
                'value' => 'false', // Si los comprobantes se envían automáticamente
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'generate_pdf',
                'default' => 'true',
                'value' => 'true', // Si se generan PDFs automáticamente
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'igv_percent',
                'default' => '10.50',
                'value' => '10.50', // Porcentaje de IGV
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'qpse_endpoint_beta',
                'default' => '',
                'value' => '', // URL del endpoint QPSE para pruebas
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'qpse_endpoint_production',
                'default' => '',
                'value' => '', // URL del endpoint QPSE para producción
            ],
        ];

        // Combinar todas las configuraciones
        $allSettings = array_merge($empresaSettings, $facturacionSettings);

        // Insertar en la base de datos
        foreach ($allSettings as $setting) {
            // Generar UUID para cada registro
            $setting['id'] = Str::uuid()->toString();

            // Añadir timestamps
            $setting['created_at'] = now();
            $setting['updated_at'] = now();

            // Insertar en la tabla app_settings
            DB::table('app_settings')->insert($setting);
        }

        $this->command->info('Configuración de la empresa y facturación electrónica insertada correctamente.');
        $this->command->warn('IMPORTANTE: Las contraseñas están cifradas. Asegúrate de implementar la lógica de descifrado con Crypt::decryptString() al momento de leer y usar estas credenciales.');
    }
}
