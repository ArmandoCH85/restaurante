# Configuración de Facturación Electrónica SUNAT

## Descripción General

Este sistema permite gestionar la configuración de facturación electrónica para SUNAT, incluyendo el cambio entre entornos (Beta/Producción) y la gestión de certificados digitales.

## Funcionalidades Implementadas

### 1. Toggle de Entorno (Beta ↔ Producción)

- **Ubicación**: `/admin/configuracion/facturacion-electronica`
- **Funcionalidad**: Permite cambiar entre entorno de pruebas (Beta) y producción
- **Características**:
  - Toggle visual que muestra el estado actual
  - Actualización automática en la base de datos
  - Indicadores visuales del entorno activo

### 2. Gestión de Certificados Digitales

- **Formatos soportados**: .p12, .pfx, .pem
- **Tamaño máximo**: 5MB
- **Almacenamiento seguro**: `storage/app/private/sunat/certificates/`
- **Separación por entorno**:
  - Beta: `storage/app/private/sunat/certificates/beta/`
  - Producción: `storage/app/private/sunat/certificates/production/`

### 3. Acciones Adicionales

#### Probar Conexión
- Valida la configuración actual
- Verifica usuario SOL y certificado
- Muestra notificaciones de estado

#### Cambiar Entorno
- Botón dinámico que cambia según el entorno actual
- Confirmación requerida para el cambio
- Actualización automática de la configuración

#### Limpiar Certificados
- Elimina todos los certificados cargados
- Confirmación requerida
- Limpia la configuración de rutas

### 4. Widget de Estado

Muestra un resumen visual de:
- Estado del entorno actual
- Estado del certificado digital
- Estado del usuario SOL

## Estructura de Directorios

```
storage/app/private/sunat/
├── certificates/
│   ├── beta/           # Certificados para entorno de pruebas
│   └── production/     # Certificados para entorno de producción
├── temp/               # Archivos temporales
├── .gitignore          # Protección de certificados
└── README.md           # Documentación del directorio
```

## Configuración en Base de Datos

Los datos se almacenan en la tabla `app_settings` con:
- `tab`: 'FacturacionElectronica'
- `key`: Nombre de la configuración
- `value`: Valor actual
- `default`: Valor por defecto

### Configuraciones Disponibles

| Key | Descripción | Valores |
|-----|-------------|---------|
| `environment` | Entorno SUNAT | beta, produccion |
| `soap_type` | Tipo de conexión | sunat, ose |
| `sol_user` | Usuario SOL | String |
| `sol_password` | Contraseña SOL | String (cifrado) |
| `certificate_path` | Ruta del certificado | String |
| `certificate_password` | Contraseña del certificado | String (cifrado) |
| `send_automatically` | Envío automático | true, false |
| `generate_pdf` | Generar PDF | true, false |
| `igv_percent` | Porcentaje IGV | 18.00 |

## Seguridad

### Cifrado de Datos Sensibles
- Las contraseñas se almacenan cifradas usando `Crypt::encryptString()`
- Los certificados se almacenan fuera del directorio público
- Archivo `.gitignore` protege los certificados del control de versiones

### Validaciones
- Verificación de tipos de archivo para certificados
- Límites de tamaño de archivo
- Confirmaciones para acciones críticas

## Comandos Artisan

### Configurar Directorios
```bash
php artisan sunat:setup-directories
```
Crea la estructura de directorios necesaria para almacenar certificados.

## Uso Recomendado

### Para Entorno de Desarrollo/Pruebas
1. Mantener el toggle en "Beta"
2. Usar credenciales de prueba de SUNAT
3. Cargar certificado de prueba

### Para Entorno de Producción
1. Cambiar el toggle a "Producción"
2. Configurar credenciales reales
3. Cargar certificado digital válido
4. Verificar conexión antes de usar

## Notas Importantes

- **Backup**: Siempre hacer backup de certificados antes de cambios
- **Credenciales**: Las credenciales de producción deben ser reales y válidas
- **Certificados**: Los certificados deben estar vigentes y ser válidos para SUNAT
- **Entorno**: Verificar siempre el entorno antes de emitir comprobantes

## Troubleshooting

### Problemas Comunes

1. **Certificado no se carga**
   - Verificar formato del archivo
   - Verificar tamaño del archivo (máx 5MB)
   - Verificar permisos de directorio

2. **Error de conexión**
   - Verificar credenciales SOL
   - Verificar certificado válido
   - Verificar entorno correcto

3. **Configuración no se guarda**
   - Verificar permisos de base de datos
   - Verificar logs de Laravel
   - Limpiar cache de configuración
