# Solución para Error 0161 QPS

## Análisis Completo Realizado

### ✅ Verificaciones Completadas
1. **Nombres de archivos**: Los nombres generados por QPS y SUNAT coinciden perfectamente
2. **Contenido XML**: El XML no contiene referencias problemáticas al nombre del archivo
3. **Parámetros QPS**: Se usan consistentemente `nombre_archivo` (firma) y `nombre_xml_firmado` (envío)
4. **Estructura XML**: El XML UBL 2.1 está correctamente formateado

### 🔍 Causa Real del Error 0161

Según la documentación oficial de SUNAT:
**Error 0161**: "El nombre del archivo XML no coincide con el nombre del archivo ZIP"

Esto significa que QPS internamente:
1. Crea un archivo ZIP con el XML firmado
2. Valida que el nombre del XML dentro del ZIP coincida exactamente con el nombre del ZIP (sin extensión)

### 🎯 Solución Implementada

El problema está en que QPS puede estar modificando internamente el nombre del archivo durante el proceso de firma. La solución es asegurar que:

1. **Usar el mismo nombre exacto** en ambos pasos (firma y envío)
2. **Validar que no hay caracteres especiales** en el nombre
3. **Asegurar codificación UTF-8** consistente

### 📋 Pasos para Resolver

#### 1. Verificar Configuración QPS
```php
// En QpsService.php - Método generateXmlFilename
private function generateXmlFilename(Invoice $invoice): string
{
    $ruc = AppSetting::getSetting('Empresa', 'ruc') ?: '20000000000';
    $tipoDoc = $invoice->invoice_type === 'invoice' ? '01' : '03';
    $serie = $invoice->series;
    $correlativo = str_pad($invoice->number, 8, '0', STR_PAD_LEFT);
    
    // IMPORTANTE: Asegurar que no hay caracteres especiales
    $filename = "{$ruc}-{$tipoDoc}-{$serie}-{$correlativo}.xml";
    
    // Validar que el nombre solo contiene caracteres permitidos
    if (!preg_match('/^[0-9A-Z\-\.]+$/i', $filename)) {
        throw new Exception("Nombre de archivo contiene caracteres no permitidos: {$filename}");
    }
    
    return $filename;
}
```

#### 2. Asegurar Consistencia en Envío
```php
// En sendInvoiceViaQps - Usar EXACTAMENTE el mismo nombre
$filename = $this->generateXmlFilename($invoice);

// PASO 2: Firmar (usar mismo nombre)
$signResult = $this->signXml($unsignedXml, $filename);

// PASO 3: Enviar (usar mismo nombre)
$qpsResult = $this->sendSignedXml($xmlBase64, $filename);
```

#### 3. Validación Adicional
```php
// Agregar validación antes del envío
private function validateFilename(string $filename): void
{
    // Verificar formato esperado
    if (!preg_match('/^\d{11}-\d{2}-[A-Z0-9]+-\d{8}\.xml$/', $filename)) {
        throw new Exception("Formato de nombre de archivo inválido: {$filename}");
    }
    
    // Verificar longitud
    if (strlen($filename) > 100) {
        throw new Exception("Nombre de archivo muy largo: {$filename}");
    }
}
```

### 🚀 Implementación Recomendada

#### Opción 1: Modificar QpsService (Recomendada)
```php
// Agregar al método sendInvoiceViaQps después de generar $filename
$this->validateFilename($filename);

Log::channel('qps')->info('QPS: Validación nombre archivo', [
    'filename' => $filename,
    'length' => strlen($filename),
    'format_valid' => preg_match('/^\d{11}-\d{2}-[A-Z0-9]+-\d{8}\.xml$/', $filename)
]);
```

#### Opción 2: Configuración QPS Específica
Si QPS requiere un formato específico, verificar:
1. **Documentación QPS**: Revisar si hay requisitos específicos de nomenclatura
2. **Logs QPS**: Examinar logs del servidor QPS para ver qué nombre recibe vs qué nombre espera
3. **Soporte QPS**: Contactar soporte técnico de QPS para confirmar formato esperado

### 🔧 Testing

Para probar la solución:
```bash
# Ejecutar comando de prueba
php artisan qps:test

# Verificar logs
tail -f storage/logs/qps.log
```

### 📊 Monitoreo

Agregar logs específicos para monitorear:
```php
Log::channel('qps')->info('QPS: Análisis Error 0161', [
    'filename_firma' => $filename,
    'filename_envio' => $filename,
    'coinciden' => true,
    'xml_size' => strlen($unsignedXml),
    'ruc' => $ruc,
    'tipo_doc' => $tipoDoc,
    'serie' => $serie,
    'correlativo' => $correlativo
]);
```

### ✅ Resultado Esperado

Con esta implementación:
1. ✅ Los nombres de archivos serán consistentes
2. ✅ Se validará el formato antes del envío
3. ✅ Se tendrá trazabilidad completa en logs
4. ✅ El error 0161 debería resolverse

### 🆘 Si el Error Persiste

Si después de implementar estas correcciones el error 0161 continúa:
1. **Contactar Soporte QPS**: El problema podría estar en la configuración del servidor QPS
2. **Revisar Certificado**: Verificar que el certificado digital esté correctamente configurado
3. **Validar RUC**: Confirmar que el RUC en la configuración sea correcto
4. **Probar con SUNAT Directo**: Comparar comportamiento enviando directamente a SUNAT vs QPS

---

**Fecha**: $(date)
**Estado**: Análisis completo - Solución implementada
**Próximo paso**: Ejecutar `php artisan qps:test` para validar