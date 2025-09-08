# Servicio QPS para Envío a SUNAT

## Descripción

El servicio QPS (`QpsService`) permite enviar comprobantes electrónicos a SUNAT a través de la plataforma **qpse.pe**, proporcionando una alternativa más estable y confiable al envío directo con Greenter.

## Características

- ✅ **Autenticación automática** con tokens de acceso
- ✅ **Integración con Greenter** para generar XML firmado
- ✅ **Manejo de errores** comprensible y detallado
- ✅ **Cache de tokens** para optimizar rendimiento
- ✅ **Ambiente de pruebas** (beta.qpse.pe)
- ✅ **Logs detallados** para debugging
- ✅ **Verificación de disponibilidad** del servicio

## Configuración

### Variables de Entorno

Agregar al archivo `.env`:

```env
# QPS Configuration (qpse.pe)
QPS_BASE_URL=https://demo-cpe.qpse.pe/api/cpe
QPS_USERNAME=IERCEST1
QPS_PASSWORD=Qrico123
QPS_ENABLED=true
```

### Configuración en services.php

```php
'qps' => [
    'base_url' => env('QPS_BASE_URL', 'https://demo-cpe.qpse.pe/api/cpe'),
    'username' => env('QPS_USERNAME'),
    'password' => env('QPS_PASSWORD'),
    'enabled' => env('QPS_ENABLED', true),
],
```

## Uso

### 1. Desde Filament Admin

1. Ir a **Facturas** en el panel de administración
2. Seleccionar una factura con estado `PENDIENTE`
3. Hacer clic en **"Enviar a SUNAT"**
4. Seleccionar **"QPS (qpse.pe)"** como método de envío
5. Confirmar el envío

### 2. Desde Código PHP

```php
use App\Services\QpsService;
use App\Models\Invoice;

// Crear instancia del servicio
$qpsService = new QpsService();

// Obtener una factura
$invoice = Invoice::find(1);

// Enviar a SUNAT vía QPS
$result = $qpsService->sendInvoiceViaQps($invoice);

if ($result['success']) {
    echo "Factura enviada exitosamente";
    echo "XML URL: " . $result['xml_url'];
    echo "CDR URL: " . $result['cdr_url'];
} else {
    echo "Error: " . $result['message'];
}
```

### 3. Comando Artisan para Pruebas

```bash
# Probar configuración y conectividad
php artisan qps:test

# Probar envío de factura específica
php artisan qps:test 123
```

## Flujo de Funcionamiento

### 1. Autenticación

```
POST /obtenertoken
{
    "usuario_empresa": "IERCEST1",
    "clave_empresa": "Qrico123"
}

Respuesta:
{
    "token_acceso": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expira_en": 3600
}
```

### 2. Envío de XML Firmado

```
POST /enviar
Headers:
    Authorization: Bearer {token_acceso}
    Content-Type: application/json

{
    "nombre_xml_firmado": "20613251988-01-F001-00000123.xml",
    "contenido_xml_firmado": "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4..."
}
```

### 3. Procesamiento Interno

1. **Verificar disponibilidad** del servicio QPS
2. **Obtener token** de acceso (con cache)
3. **Generar XML** usando Greenter y SunatService
4. **Firmar XML** con certificado digital
5. **Enviar a QPS** en formato base64
6. **Procesar respuesta** y actualizar estado en BD
7. **Guardar archivos** XML y CDR

## Métodos Principales

### QpsService::sendInvoiceViaQps($invoice)

Envía una factura completa a SUNAT vía QPS.

**Parámetros:**
- `$invoice`: Modelo Invoice de Laravel

**Retorna:**
```php
[
    'success' => true|false,
    'message' => 'Mensaje descriptivo',
    'xml_url' => 'URL del archivo XML',
    'cdr_url' => 'URL del archivo CDR',
    'sunat_response' => [...] // Respuesta completa de SUNAT
]
```

### QpsService::getAccessToken()

Obtiene un token de acceso válido (con cache automático).

### QpsService::sendSignedXml($xmlFilename, $xmlContent)

Envía XML firmado directamente a QPS.

### QpsService::isServiceAvailable()

Verifica si el servicio QPS está disponible.

### QpsService::getConfiguration()

Obtiene la configuración actual del servicio para debugging.

## Manejo de Errores

El servicio maneja automáticamente los siguientes tipos de errores:

- **Errores de conectividad** con QPS
- **Tokens expirados** (renovación automática)
- **Errores de SUNAT** (códigos específicos)
- **Problemas de certificado** digital
- **Errores de validación** de XML

## Logs

Todos los eventos importantes se registran en:

```
storage/logs/laravel.log
```

Con el canal `QPS Service` para fácil filtrado.

## Ventajas sobre Envío Directo

| Aspecto | Envío Directo | QPS Service |
|---------|---------------|-------------|
| **Estabilidad** | Media | Alta |
| **Manejo de errores** | Básico | Avanzado |
| **Reintentos** | Manual | Automático |
| **Monitoreo** | Limitado | Completo |
| **Soporte** | Comunidad | Profesional |
| **Uptime** | Variable | 99.9% |

## Troubleshooting

### Error: "QPS service is not available"

1. Verificar conectividad a internet
2. Comprobar que `QPS_ENABLED=true` en `.env`
3. Verificar URL base en configuración

### Error: "Invalid credentials"

1. Verificar `QPS_USERNAME` y `QPS_PASSWORD` en `.env`
2. Confirmar que las credenciales son válidas en qpse.pe

### Error: "XML generation failed"

1. Verificar configuración de Greenter
2. Comprobar certificado digital
3. Revisar datos de la factura

### Error: "Token expired"

El servicio maneja esto automáticamente, pero si persiste:

1. Limpiar cache: `php artisan cache:clear`
2. Verificar conectividad con QPS

## Ambiente de Producción

Para usar en producción, cambiar:

```env
QPS_BASE_URL=https://cpe.qpse.pe/api/cpe
```

**Nota:** Asegurarse de tener credenciales válidas para el ambiente de producción.

## Soporte

Para problemas específicos con QPS:
- Documentación: https://qpse.pe/docs
- Soporte: soporte@qpse.pe

Para problemas con la integración:
- Revisar logs en `storage/logs/laravel.log`
- Usar comando de prueba: `php artisan qps:test`