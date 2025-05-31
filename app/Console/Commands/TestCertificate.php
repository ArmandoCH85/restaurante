<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TestCertificate extends Command
{
    protected $signature = 'sunat:test-certificate';
    protected $description = 'Probar certificado SUNAT con diferentes métodos';

    public function handle()
    {
        $this->info('🔍 Probando certificado SUNAT...');
        
        // Obtener configuración
        $certificatePath = AppSetting::getSetting('FacturacionElectronica', 'certificate_path');
        $certificatePassword = AppSetting::getSetting('FacturacionElectronica', 'certificate_password');
        
        if (!$certificatePath || !$certificatePassword) {
            $this->error('❌ Certificado o contraseña no configurados');
            return 1;
        }
        
        if (!File::exists($certificatePath)) {
            $this->error('❌ Archivo de certificado no encontrado: ' . $certificatePath);
            return 1;
        }
        
        $this->info('📁 Certificado: ' . $certificatePath);
        $this->info('🔑 Contraseña: ' . str_repeat('*', strlen($certificatePassword)));
        $this->info('📏 Tamaño: ' . File::size($certificatePath) . ' bytes');
        
        // Leer contenido del certificado
        $certificateContent = File::get($certificatePath);
        
        $this->info('');
        $this->info('🧪 Probando métodos de procesamiento...');
        
        // Método 1: openssl_pkcs12_read
        $this->testOpenSSLPkcs12Read($certificateContent, $certificatePassword);
        
        // Método 2: Comando openssl externo
        $this->testOpenSSLCommand($certificatePath, $certificatePassword);
        
        // Método 3: Verificar si OpenSSL está disponible
        $this->testOpenSSLAvailability();
        
        return 0;
    }
    
    private function testOpenSSLPkcs12Read($certificateContent, $certificatePassword)
    {
        $this->info('');
        $this->info('1️⃣ Probando openssl_pkcs12_read...');
        
        try {
            $certs = [];
            if (openssl_pkcs12_read($certificateContent, $certs, $certificatePassword)) {
                $this->info('   ✅ openssl_pkcs12_read: ÉXITO');
                $this->info('   📋 Componentes encontrados:');
                $this->info('      - Certificado: ' . (isset($certs['cert']) ? '✅' : '❌'));
                $this->info('      - Clave privada: ' . (isset($certs['pkey']) ? '✅' : '❌'));
                $this->info('      - Certificados extra: ' . (isset($certs['extracerts']) ? count($certs['extracerts']) : 0));
                
                // Probar validación de clave privada
                if (isset($certs['pkey'])) {
                    $keyResource = openssl_pkey_get_private($certs['pkey']);
                    if ($keyResource !== false) {
                        $this->info('      - Validación clave privada: ✅');
                        $keyDetails = openssl_pkey_get_details($keyResource);
                        $this->info('      - Tipo de clave: ' . ($keyDetails['type'] ?? 'unknown'));
                        $this->info('      - Bits: ' . ($keyDetails['bits'] ?? 'unknown'));
                    } else {
                        $this->error('      - Validación clave privada: ❌ ' . openssl_error_string());
                    }
                }
            } else {
                $this->error('   ❌ openssl_pkcs12_read: FALLÓ');
                $this->error('   🔍 Error: ' . openssl_error_string());
            }
        } catch (\Exception $e) {
            $this->error('   ❌ openssl_pkcs12_read: EXCEPCIÓN');
            $this->error('   🔍 Error: ' . $e->getMessage());
        }
    }
    
    private function testOpenSSLCommand($certificatePath, $certificatePassword)
    {
        $this->info('');
        $this->info('2️⃣ Probando comando openssl externo...');
        
        // Crear archivo temporal para salida
        $pemFile = tempnam(sys_get_temp_dir(), 'test_cert_') . '.pem';
        
        try {
            // Comando básico
            $command = sprintf(
                'openssl pkcs12 -in "%s" -out "%s" -nodes -passin pass:"%s" 2>&1',
                $certificatePath,
                $pemFile,
                escapeshellarg($certificatePassword)
            );
            
            $this->info('   🔧 Comando: openssl pkcs12 -in [cert] -out [pem] -nodes -passin pass:[***]');
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($pemFile) && filesize($pemFile) > 0) {
                $this->info('   ✅ Comando openssl básico: ÉXITO');
                $this->info('   📏 PEM generado: ' . filesize($pemFile) . ' bytes');
            } else {
                $this->error('   ❌ Comando openssl básico: FALLÓ');
                $this->error('   🔍 Código retorno: ' . $returnCode);
                $this->error('   🔍 Salida: ' . implode(' ', $output));
                
                // Intentar con flag -legacy
                $this->info('   🔄 Intentando con flag -legacy...');
                
                $commandLegacy = sprintf(
                    'openssl pkcs12 -in "%s" -out "%s" -nodes -legacy -passin pass:"%s" 2>&1',
                    $certificatePath,
                    $pemFile,
                    escapeshellarg($certificatePassword)
                );
                
                $outputLegacy = [];
                $returnCodeLegacy = 0;
                exec($commandLegacy, $outputLegacy, $returnCodeLegacy);
                
                if ($returnCodeLegacy === 0 && file_exists($pemFile) && filesize($pemFile) > 0) {
                    $this->info('   ✅ Comando openssl con -legacy: ÉXITO');
                    $this->info('   📏 PEM generado: ' . filesize($pemFile) . ' bytes');
                } else {
                    $this->error('   ❌ Comando openssl con -legacy: FALLÓ');
                    $this->error('   🔍 Código retorno: ' . $returnCodeLegacy);
                    $this->error('   🔍 Salida: ' . implode(' ', $outputLegacy));
                }
            }
            
        } finally {
            // Limpiar archivo temporal
            if (file_exists($pemFile)) {
                unlink($pemFile);
            }
        }
    }
    
    private function testOpenSSLAvailability()
    {
        $this->info('');
        $this->info('3️⃣ Verificando disponibilidad de OpenSSL...');
        
        // Verificar comando openssl
        $output = [];
        $returnCode = 0;
        exec('openssl version 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info('   ✅ OpenSSL disponible: ' . implode(' ', $output));
        } else {
            $this->error('   ❌ OpenSSL no disponible en PATH del sistema');
            $this->error('   🔍 Salida: ' . implode(' ', $output));
            
            // Sugerir ubicaciones comunes
            $this->info('   💡 Ubicaciones comunes de OpenSSL:');
            $this->info('      - C:\\Program Files\\OpenSSL\\bin\\openssl.exe');
            $this->info('      - C:\\OpenSSL\\bin\\openssl.exe');
            $this->info('      - Incluido con Git: C:\\Program Files\\Git\\usr\\bin\\openssl.exe');
        }
        
        // Verificar extensión OpenSSL de PHP
        if (extension_loaded('openssl')) {
            $this->info('   ✅ Extensión OpenSSL de PHP: Cargada');
            $this->info('   📋 Versión: ' . OPENSSL_VERSION_TEXT);
        } else {
            $this->error('   ❌ Extensión OpenSSL de PHP: No cargada');
        }
    }
}
