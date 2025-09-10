# 🔧 Solución para Facturas Atascadas en Estado "ENVIANDO"

## 📋 Descripción del Problema

### 🚨 Síntomas
- Facturas quedan en estado `ENVIANDO` indefinidamente
- Error de timeout de 30 segundos durante el envío
- Comprobantes no se actualizan a `ACEPTADO` o `RECHAZADO`
- Usuarios no pueden reenviar desde la interfaz normal

### 🔍 Causas Comunes
1. **Timeout de conexión**: SUNAT no responde en 30 segundos
2. **Sobrecarga del servidor SUNAT**: Especialmente en horas pico
3. **Problemas de red**: Conexión inestable o lenta
4. **Certificados vencidos**: Certificado digital expirado
5. **Errores de configuración**: Endpoints incorrectos

---

## 🛠️ Soluciones Implementadas

### 1. 🖥️ **Interfaz Web** (Recomendado para casos individuales)

#### Pasos:
1. Ir a `/admin/invoices`
2. Filtrar por estado "Enviando" o "Error"
3. Hacer clic en "Editar" en la factura problemática
4. Buscar el botón **"Corregir Envío"** (color azul)
5. Seleccionar método de reenvío:
   - **QPS (Recomendado)**: Más estable, maneja timeouts
   - **SUNAT Directo**: Conexión directa (menos estable)
6. Agregar motivo de la corrección
7. Confirmar la acción

#### Ventajas:
✅ Interfaz visual intuitiva  
✅ Confirmación paso a paso  
✅ Logs automáticos de la acción  
✅ Feedback inmediato del resultado  

---

### 2. 💻 **Comando Artisan** (Recomendado para casos masivos)

#### Comando Principal:
```bash
php artisan sunat:fix-stuck-invoices
```

#### Parámetros Disponibles:

| Parámetro | Descripción | Default | Ejemplo |
|-----------|-------------|---------|----------|
| `--status` | Estado a corregir | `ENVIANDO` | `--status=ERROR` |
| `--hours` | Horas desde última actualización | `2` | `--hours=4` |
| `--method` | Método de reenvío | `qps` | `--method=sunat` |
| `--dry-run` | Solo mostrar, no ejecutar | - | `--dry-run` |
| `--force` | No pedir confirmación | - | `--force` |
| `--invoice-id` | Corregir factura específica | - | `--invoice-id=123` |

#### Ejemplos de Uso:

```bash
# 🔍 Ver qué facturas se corregirían (sin ejecutar)
php artisan sunat:fix-stuck-invoices --dry-run

# 🔧 Corregir facturas ENVIANDO de las últimas 2 horas
php artisan sunat:fix-stuck-invoices --method=qps

# ⚡ Corregir facturas ERROR de las últimas 4 horas
php artisan sunat:fix-stuck-invoices --status=ERROR --hours=4 --method=qps

# 🎯 Corregir una factura específica
php artisan sunat:fix-stuck-invoices --invoice-id=123 --method=qps

# 🚀 Corrección masiva sin confirmación
php artisan sunat:fix-stuck-invoices --force --method=qps

# 🌙 Corrección nocturna de todo el día
php artisan sunat:fix-stuck-invoices --hours=24 --force --method=qps
```

---

## 🎯 Casos de Uso Específicos

### 📋 **Caso 1: Timeout de 30 segundos**
**Problema**: Facturas quedan en ENVIANDO por timeout  
**Solución**:
```bash
php artisan sunat:fix-stuck-invoices --method=qps
```
**Por qué funciona**: QPS maneja mejor los timeouts y reintentos

### 📋 **Caso 2: Error de conexión SUNAT**
**Problema**: Facturas en estado ERROR por fallas de red  
**Solución**:
```bash
php artisan sunat:fix-stuck-invoices --status=ERROR --method=qps
```
**Por qué funciona**: Resetea el estado y usa conexión más estable

### 📋 **Caso 3: Corrección masiva nocturna**
**Problema**: Múltiples facturas atascadas del día  
**Solución**:
```bash
php artisan sunat:fix-stuck-invoices --hours=24 --force --method=qps
```
**Por qué funciona**: Procesa todas las facturas problemáticas automáticamente

### 📋 **Caso 4: Factura urgente específica**
**Problema**: Una factura importante atascada  
**Solución**:
```bash
php artisan sunat:fix-stuck-invoices --invoice-id=123 --force --method=qps
```
**Por qué funciona**: Corrección inmediata sin esperar

---

## 🔄 Automatización

### ⏰ **Cron Job Recomendado**

Agregar a crontab para corrección automática cada 2 horas:

```bash
# Corrección automática cada 2 horas
0 */2 * * * cd /ruta/proyecto && php artisan sunat:fix-stuck-invoices --force --method=qps >> /var/log/fix-stuck-invoices.log 2>&1

# Corrección nocturna completa
0 2 * * * cd /ruta/proyecto && php artisan sunat:fix-stuck-invoices --hours=24 --force --method=qps >> /var/log/fix-stuck-invoices-nightly.log 2>&1
```

### 📊 **Monitoreo Automático**

```bash
# Script de monitoreo
#!/bin/bash
STUCK_COUNT=$(php artisan sunat:fix-stuck-invoices --dry-run | grep -c "Encontradas")
if [ $STUCK_COUNT -gt 0 ]; then
    echo "⚠️ Facturas atascadas detectadas: $STUCK_COUNT"
    php artisan sunat:fix-stuck-invoices --force --method=qps
fi
```

---

## 🔍 Diagnóstico y Monitoreo

### 📝 **Verificar Facturas Atascadas**

```sql
-- Consulta SQL para identificar facturas atascadas
SELECT 
    id,
    CONCAT(series, '-', number) as comprobante,
    invoice_type,
    sunat_status,
    total,
    updated_at,
    TIMESTAMPDIFF(HOUR, updated_at, NOW()) as horas_atascada
FROM invoices 
WHERE sunat_status IN ('ENVIANDO', 'ERROR')
    AND invoice_type IN ('invoice', 'receipt')
    AND updated_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
ORDER BY updated_at ASC;
```

### 📊 **Dashboard de Estados**

```sql
-- Resumen de estados SUNAT
SELECT 
    sunat_status,
    COUNT(*) as cantidad,
    SUM(total) as total_soles
FROM invoices 
WHERE invoice_type IN ('invoice', 'receipt')
    AND DATE(created_at) = CURDATE()
GROUP BY sunat_status
ORDER BY cantidad DESC;
```

### 📈 **Métricas de Rendimiento**

```bash
# Ver logs de corrección
tail -f storage/logs/laravel.log | grep "Corrección manual de factura"

# Estadísticas de éxito
grep "Comprobante Corregido" storage/logs/laravel.log | wc -l

# Errores recientes
grep "Error en Corrección" storage/logs/laravel.log | tail -10
```

---

## ⚠️ Prevención

### 🛡️ **Mejores Prácticas**

1. **Usar QPS por defecto**:
   ```php
   // En configuración
   'default_send_method' => 'qps'
   ```

2. **Configurar timeouts apropiados**:
   ```php
   'sunat_timeout' => 60, // 60 segundos en lugar de 30
   ```

3. **Implementar reintentos automáticos**:
   ```php
   'max_retries' => 3,
   'retry_delay' => 5 // segundos
   ```

4. **Monitoreo proactivo**:
   - Alertas cuando hay más de 5 facturas atascadas
   - Dashboard en tiempo real de estados SUNAT
   - Notificaciones por email/Slack

### 🔧 **Configuración Recomendada**

```php
// config/sunat.php
return [
    'default_method' => 'qps',
    'timeout' => 60,
    'max_retries' => 3,
    'retry_delay' => 5,
    'auto_fix_stuck' => true,
    'stuck_threshold_hours' => 2,
    'monitoring' => [
        'enabled' => true,
        'alert_threshold' => 5,
        'notification_email' => 'admin@empresa.com'
    ]
];
```

---

## 🆘 Solución de Problemas

### ❌ **Errores Comunes**

| Error | Causa | Solución |
|-------|-------|----------|
| "No se encontraron facturas" | No hay facturas atascadas | Normal, no requiere acción |
| "Error de certificado" | Certificado vencido/inválido | Renovar certificado SUNAT |
| "Timeout persiste" | Problemas de red/SUNAT | Usar método QPS |
| "Error de permisos" | Permisos de archivos | `chmod 755` en directorios |
| "QPS no disponible" | Servicio QPS inactivo | Verificar configuración QPS |

### 🔧 **Comandos de Diagnóstico**

```bash
# Verificar configuración
php artisan qpse:test-config

# Probar conexión SUNAT
php artisan sunat:test-connection

# Verificar certificados
php artisan sunat:check-certificates

# Limpiar cache
php artisan config:clear
php artisan cache:clear
```

---

## 📚 Referencias

### 🔗 **Enlaces Útiles**
- [Documentación SUNAT](https://cpe.sunat.gob.pe/)
- [QPS Service Documentation](QPS_SERVICE.md)
- [Logs de SUNAT](QPS_LOGGING.md)
- [Configuración Greenter](https://greenter.dev/)

### 📖 **Archivos Relacionados**
- `app/Console/Commands/FixStuckInvoices.php` - Comando principal
- `app/Filament/Resources/InvoiceResource/Pages/EditInvoice.php` - Interfaz web
- `fix_stuck_invoices_example.php` - Ejemplos de uso
- `app/Services/QpsService.php` - Servicio QPS
- `app/Services/SunatService.php` - Servicio SUNAT

---

## ✅ Checklist de Implementación

- [x] ✅ Comando artisan `sunat:fix-stuck-invoices` creado
- [x] ✅ Interfaz web con botón "Corregir Envío" agregada
- [x] ✅ Soporte para métodos QPS y SUNAT directo
- [x] ✅ Logs detallados de todas las acciones
- [x] ✅ Validaciones y manejo de errores robusto
- [x] ✅ Documentación completa y ejemplos
- [x] ✅ Modo dry-run para pruebas seguras
- [x] ✅ Soporte para corrección masiva y específica

---

## 🎉 Resultado Final

**El sistema ahora puede**:

🔧 **Corregir automáticamente** facturas atascadas  
🖥️ **Interfaz visual** para correcciones manuales  
📊 **Monitoreo completo** de estados y errores  
⚡ **Prevención proactiva** de futuros problemas  
🤖 **Automatización** con cron jobs  
📝 **Logs detallados** para auditoría  

**¡Las facturas atascadas ya no serán un problema!** 🚀