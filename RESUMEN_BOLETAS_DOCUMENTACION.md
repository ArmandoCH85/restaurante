# 📋 Resúmenes de Boletas SUNAT - Documentación Completa

## 🎯 Descripción General

Este sistema implementa la funcionalidad de **Resúmenes de Boletas** para SUNAT siguiendo el proceso **QPSE (Qualified Process for Summary Emission)**, que permite reportar boletas de venta de forma agrupada y asíncrona.

### ✨ Características Principales

- ✅ **Proceso Asíncrono**: Envío mediante tickets de SUNAT
- ✅ **Interfaz Web**: Panel administrativo completo
- ✅ **Comandos CLI**: Automatización via Artisan
- ✅ **Validaciones**: Horarios, fechas y datos requeridos
- ✅ **Logs Completos**: Seguimiento detallado de todos los procesos
- ✅ **Archivos XML/CDR**: Generación y almacenamiento automático

---

## 🏗️ Arquitectura del Sistema

### 📁 Archivos Implementados

```
app/
├── Services/
│   └── SunatService.php              # Métodos principales agregados
├── Http/Controllers/
│   └── SummaryController.php          # Controlador web
├── Console/Commands/
│   ├── SendDailySummary.php           # Comando para enviar resúmenes
│   └── CheckSummaryStatus.php         # Comando para consultar estado

resources/views/admin/summaries/
└── index.blade.php                    # Interfaz web

routes/
└── web.php                            # Rutas agregadas

ejemplo_resumen_boletas.php            # Ejemplo de uso
```

### 🔧 Métodos Agregados a SunatService

1. **`enviarResumenBoletas()`** - Envía resumen a SUNAT
2. **`createGreenterSummary()`** - Crea estructura Greenter
3. **`getNextSummaryCorrelativo()`** - Genera correlativo único
4. **`saveSummaryXmlFile()`** - Guarda archivo XML
5. **`consultarEstadoResumen()`** - Consulta estado por ticket
6. **`interpretarCodigoEstado()`** - Interpreta códigos de respuesta

---

## 🚀 Guía de Uso

### 1. 🌐 Interfaz Web

#### Acceso
```
http://tu-dominio.com/admin/summaries
```

#### Funcionalidades
- **Vista Previa**: Ver boletas antes de enviar
- **Generación**: Crear y enviar resúmenes
- **Consulta**: Verificar estado de resúmenes enviados
- **Validaciones**: Fechas, horarios y datos automáticos

#### Proceso Paso a Paso
1. Seleccionar **fecha de referencia** (día de las boletas)
2. Hacer clic en **"Vista Previa"** para verificar boletas
3. Hacer clic en **"Generar y Enviar Resumen"**
4. Copiar el **ticket** recibido
5. Usar el ticket para **consultar el estado**

### 2. 🖥️ Comandos CLI

#### Enviar Resumen Diario
```bash
# Enviar resumen de ayer
php artisan sunat:send-daily-summary

# Enviar resumen de fecha específica
php artisan sunat:send-daily-summary --date=2024-01-15

# Simulación (no envía a SUNAT)
php artisan sunat:send-daily-summary --dry-run

# Forzar envío fuera de horario
php artisan sunat:send-daily-summary --force
```

#### Consultar Estado
```bash
# Consulta simple
php artisan sunat:check-summary-status 20240115123456789012

# Con reintentos automáticos
php artisan sunat:check-summary-status 20240115123456789012 --retry=3 --interval=30

# Esperar antes de consultar
php artisan sunat:check-summary-status 20240115123456789012 --wait=60
```

### 3. 💻 Uso Programático

```php
use App\Services\SunatService;

$sunatService = new SunatService();

// Preparar datos de boletas
$boletas = [
    [
        'series' => 'B001',
        'number' => '00000123',
        'invoice_type' => 'receipt',
        'total' => 118.00,
        'subtotal' => 100.00,
        'igv' => 18.00,
        'customer_document_type' => 'DNI',
        'customer_document_number' => '12345678',
        'estado' => '1' // 1=Adicionar, 2=Modificar, 3=Anular
    ]
    // ... más boletas
];

// Enviar resumen
$resultado = $sunatService->enviarResumenBoletas(
    $boletas, 
    '2024-01-16', // fecha generación
    '2024-01-15'  // fecha referencia
);

if ($resultado['success']) {
    $ticket = $resultado['ticket'];
    
    // Consultar estado después
    $estado = $sunatService->consultarEstadoResumen($ticket);
}
```

---

## ⚙️ Configuración y Requisitos

### 📋 Requisitos Previos

1. **SunatService configurado** con certificados válidos
2. **Greenter instalado** con clases Summary y SummaryDetail
3. **Boletas en estado ACEPTADO** en la base de datos
4. **Permisos de escritura** en `storage/app/sunat/summaries/`

### 🔧 Configuración de Horarios

```php
// En SunatService o configuración
const HORARIO_LIMITE_RESUMEN = '12:00'; // 12:00 PM
```

### 📁 Estructura de Archivos

```
storage/app/sunat/summaries/
├── xml/
│   └── RC-YYYYMMDD-001.xml
└── cdr/
    └── R-RC-YYYYMMDD-001.zip
```

---

## 📊 Estados y Códigos SUNAT

### 🎫 Estados del Resumen

| Código | Estado | Descripción |
|--------|--------|-------------|
| `0` | ✅ **ACEPTADO** | Resumen procesado exitosamente |
| `98` | ⏳ **EN_PROCESO** | SUNAT está procesando el resumen |
| `99` | ❌ **PROCESADO_CON_ERRORES** | Errores en el procesamiento |

### 🔄 Estados de Boletas

| Código | Acción | Uso |
|--------|--------|-----|
| `1` | **Adicionar** | Boletas nuevas (uso normal) |
| `2` | **Modificar** | Correcciones de boletas |
| `3` | **Anular** | Anulación de boletas |

---

## 🕐 Reglas de Negocio

### ⏰ Horarios de Envío
- **Límite**: 12:00 PM del día siguiente
- **Recomendado**: Enviar en la mañana
- **Forzar**: Usar `--force` para envíos fuera de horario

### 📅 Fechas Válidas
- **Fecha de referencia**: Debe ser anterior a hoy
- **Fecha de generación**: Puede ser hoy o posterior a la referencia
- **Máximo**: 7 días de diferencia (recomendación)

### 📋 Boletas Incluidas
- Solo boletas con `invoice_type = 'receipt'`
- Boletas con `sunat_status IN ('ACEPTADO', 'PENDIENTE')`
- Mínimo 1 boleta por resumen

---

## 🔍 Monitoreo y Logs

### 📝 Logs del Sistema

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep -i "resumen\|summary"

# Buscar logs específicos
grep "enviarResumenBoletas" storage/logs/laravel.log
```

### 🎯 Eventos Registrados

- ✅ Inicio de generación de resumen
- ✅ Envío exitoso con ticket
- ✅ Consultas de estado
- ❌ Errores de validación
- ❌ Errores de comunicación con SUNAT
- ❌ Errores críticos del sistema

---

## 🚨 Solución de Problemas

### ❌ Errores Comunes

#### "No se encontraron boletas"
```bash
# Verificar boletas en BD
php artisan tinker
>>> App\Models\Invoice::where('invoice_type', 'receipt')
    ->whereDate('issue_date', '2024-01-15')
    ->whereIn('sunat_status', ['ACEPTADO', 'PENDIENTE'])
    ->count()
```

#### "Error de certificado"
- Verificar ruta del certificado en `AppSetting`
- Verificar permisos de lectura del archivo
- Verificar que el certificado no haya expirado

#### "Horario no permitido"
```bash
# Forzar envío
php artisan sunat:send-daily-summary --force
```

#### "Ticket inválido"
- El ticket debe tener exactamente 20 dígitos
- Formato: `YYYYMMDDHHMMSSNNNNNN`
- Verificar que no tenga espacios o caracteres especiales

### 🔧 Comandos de Diagnóstico

```bash
# Verificar configuración SUNAT
php artisan tinker
>>> $service = new App\Services\SunatService()
>>> $service->getSee() // Debe retornar instancia Greenter

# Verificar permisos de archivos
ls -la storage/app/sunat/summaries/

# Verificar logs recientes
tail -n 50 storage/logs/laravel.log
```

---

## 🔄 Automatización

### ⏰ Cron Jobs

```bash
# Agregar a crontab para envío automático diario a las 8:00 AM
0 8 * * * cd /ruta/proyecto && php artisan sunat:send-daily-summary

# Consultar estado automáticamente cada hora
0 * * * * cd /ruta/proyecto && php artisan sunat:check-summary-status $(cat /tmp/ultimo_ticket.txt)
```

### 🔔 Notificaciones

```php
// En el controlador o comando, agregar notificaciones
use Illuminate\Support\Facades\Mail;

// Después de envío exitoso
Mail::to('admin@empresa.com')->send(
    new ResumenEnviadoMail($ticket, $correlativo)
);
```

---

## 📚 Referencias

### 🔗 Enlaces Útiles

- [Documentación SUNAT - Resúmenes](https://cpe.sunat.gob.pe/)
- [Greenter Documentation](https://greenter.dev/)
- [Laravel Artisan Commands](https://laravel.com/docs/artisan)

### 📖 Documentos SUNAT

- Manual de Usuario - Facturación Electrónica
- Especificaciones Técnicas UBL 2.1
- Códigos de Respuesta SUNAT

---

## 🆘 Soporte

### 📞 Contacto

Para soporte técnico:
1. Revisar logs del sistema
2. Verificar configuración SUNAT
3. Consultar documentación SUNAT
4. Contactar al administrador del sistema

### 🐛 Reporte de Bugs

Incluir en el reporte:
- Fecha y hora del error
- Mensaje de error completo
- Logs relevantes
- Pasos para reproducir
- Configuración del entorno

---

## 📝 Notas Finales

- ✅ Sistema probado con entorno de desarrollo SUNAT
- ✅ Compatible con Laravel 10+
- ✅ Sigue estándares PSR-4
- ✅ Documentación completa incluida
- ✅ Manejo robusto de errores

**¡El sistema está listo para producción!** 🚀