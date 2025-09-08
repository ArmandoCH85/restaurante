# Sistema de Logging QPS

Este documento explica el sistema de logging específico implementado para el servicio QPS, que permite monitorear las peticiones y operaciones de manera separada del log principal de Laravel.

## Configuración

### Canal de Logging QPS

Se ha configurado un canal específico llamado `qps` en `config/logging.php`:

```php
'qps' => [
    'driver' => 'daily',
    'path' => storage_path('logs/qps.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 30,
    'replace_placeholders' => true,
],
```

### Características del Canal QPS

- **Archivo separado**: Los logs se guardan en `storage/logs/qps.log`
- **Rotación diaria**: Se crea un nuevo archivo cada día
- **Retención**: Se mantienen 30 días de logs
- **Nivel**: Respeta la configuración `LOG_LEVEL` del `.env`

## Ubicación de los Logs

### Logs QPS
```
storage/logs/qps-YYYY-MM-DD.log
```

### Logs Generales de Laravel
```
storage/logs/laravel-YYYY-MM-DD.log
```

## Tipos de Eventos Registrados

### 1. Autenticación
```
[YYYY-MM-DD HH:MM:SS] local.INFO: QPS: Solicitando nuevo token de acceso
[YYYY-MM-DD HH:MM:SS] local.INFO: QPS: Token obtenido exitosamente {"token_prefix":"eyJ0eXAiOi...","expira_en_segundos":3600}
[YYYY-MM-DD HH:MM:SS] local.ERROR: QPS: Error al obtener token {"error":"cURL error 6: Could not resolve host"}
```

### 2. Envío de Comprobantes
```
[YYYY-MM-DD HH:MM:SS] local.INFO: QPS: Iniciando envío de factura {"invoice_id":123,"series_number":"F001-00001"}
[YYYY-MM-DD HH:MM:SS] local.INFO: QPS: Enviando XML firmado a SUNAT {"filename":"20123456789-01-F001-00001.xml","xml_size":2048}
[YYYY-MM-DD HH:MM:SS] local.INFO: QPS: Respuesta recibida de SUNAT {"filename":"20123456789-01-F001-00001.xml","success":true}
[YYYY-MM-DD HH:MM:SS] local.INFO: QPS: Factura enviada exitosamente {"invoice_id":123,"document_name":"F001-00001"}
```

### 3. Errores
```
[YYYY-MM-DD HH:MM:SS] local.ERROR: QPS: Error al enviar XML {"filename":"20123456789-01-F001-00001.xml","error":"HTTP 500"}
[YYYY-MM-DD HH:MM:SS] local.ERROR: QPS: Error al enviar factura {"invoice_id":123,"error":"Token expirado"}
[YYYY-MM-DD HH:MM:SS] local.ERROR: QPS Test Command Error {"error":"Connection timeout","trace":"..."}
```

## Comandos para Monitoreo

### Ver logs en tiempo real
```bash
# Logs QPS únicamente
tail -f storage/logs/qps-$(date +%Y-%m-%d).log

# Logs generales de Laravel
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Buscar errores específicos
```bash
# Errores de conexión
grep -i "could not resolve host" storage/logs/qps-*.log

# Errores de token
grep -i "token" storage/logs/qps-*.log | grep -i error

# Facturas enviadas exitosamente
grep "Factura enviada exitosamente" storage/logs/qps-*.log
```

### Filtrar por fecha
```bash
# Logs de hoy
cat storage/logs/qps-$(date +%Y-%m-%d).log

# Logs de ayer
cat storage/logs/qps-$(date -d "yesterday" +%Y-%m-%d).log
```

## Análisis de Logs

### Verificar Conectividad
```bash
# Buscar errores de DNS/conectividad
grep -E "(Could not resolve host|Connection timeout|Connection refused)" storage/logs/qps-*.log
```

### Monitorear Tokens
```bash
# Ver obtención de tokens
grep "Token obtenido exitosamente" storage/logs/qps-*.log

# Ver uso de tokens existentes
grep "Usando token existente válido" storage/logs/qps-*.log
```

### Estadísticas de Envío
```bash
# Contar facturas enviadas exitosamente
grep -c "Factura enviada exitosamente" storage/logs/qps-*.log

# Contar errores de envío
grep -c "Error al enviar factura" storage/logs/qps-*.log
```

## Integración con Herramientas de Monitoreo

### Logrotate (Linux)
```bash
# /etc/logrotate.d/qps-logs
/path/to/storage/logs/qps-*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
}
```

### Alertas Automáticas
```bash
# Script para alertar sobre errores críticos
#!/bin/bash
ERROR_COUNT=$(grep -c "ERROR" storage/logs/qps-$(date +%Y-%m-%d).log)
if [ $ERROR_COUNT -gt 5 ]; then
    echo "ALERTA: $ERROR_COUNT errores QPS detectados hoy" | mail -s "QPS Alert" admin@empresa.com
fi
```

## Troubleshooting

### Problemas Comunes

1. **Archivo de log no se crea**
   - Verificar permisos en `storage/logs/`
   - Ejecutar: `php artisan cache:clear`

2. **Logs muy grandes**
   - Verificar configuración de rotación
   - Ajustar nivel de log en `.env`

3. **No se registran eventos**
   - Verificar que `LOG_LEVEL` permita el nivel deseado
   - Comprobar que el canal `qps` esté configurado correctamente

### Comandos de Diagnóstico
```bash
# Verificar configuración de logging
php artisan config:show logging.channels.qps

# Probar logging QPS
php artisan qps:test

# Limpiar cache de configuración
php artisan config:clear
```

## Mejores Prácticas

1. **Monitoreo Regular**: Revisar logs diariamente
2. **Alertas Automáticas**: Configurar notificaciones para errores críticos
3. **Análisis de Tendencias**: Identificar patrones en errores
4. **Limpieza Periódica**: Mantener solo logs necesarios
5. **Backup**: Respaldar logs importantes antes de la rotación

## Niveles de Log

- **INFO**: Operaciones normales (tokens, envíos exitosos)
- **ERROR**: Errores que requieren atención
- **DEBUG**: Información detallada para desarrollo

## Ejemplo de Sesión Completa

```
[2024-01-15 10:30:15] local.INFO: QPS: Solicitando nuevo token de acceso
[2024-01-15 10:30:16] local.INFO: QPS: Token obtenido exitosamente {"token_prefix":"eyJ0eXAiOi...","expira_en_segundos":3600}
[2024-01-15 10:30:20] local.INFO: QPS: Iniciando envío de factura {"invoice_id":123,"series_number":"F001-00001"}
[2024-01-15 10:30:22] local.INFO: QPS: Enviando XML firmado a SUNAT {"filename":"20123456789-01-F001-00001.xml"}
[2024-01-15 10:30:25] local.INFO: QPS: Respuesta recibida de SUNAT {"filename":"20123456789-01-F001-00001.xml","success":true}
[2024-01-15 10:30:25] local.INFO: QPS: Factura enviada exitosamente {"invoice_id":123,"document_name":"F001-00001"}
```

Este sistema de logging permite un monitoreo detallado y separado de todas las operaciones QPS, facilitando el diagnóstico y la resolución de problemas.