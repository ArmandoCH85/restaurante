# Configuración de Series para Notas de Crédito

## Descripción General

Este documento explica cómo configurar e inicializar las series de documentos para notas de crédito en el sistema de facturación electrónica.

## Acceso a la Configuración

Para configurar las series de notas de crédito, accede a:

**URL:** `http://restaurante.test/admin/document-series/create`

**Navegación:** Panel de Administración → Facturación y Ventas → Series de Comprobantes → Crear

## Configuración de Serie para Notas de Crédito

### Pasos para Crear una Nueva Serie

1. **Acceder al formulario de creación**
   - Ve a `http://restaurante.test/admin/document-series/create`
   - O navega desde el menú: Facturación y Ventas → Series de Comprobantes → Crear

2. **Completar el formulario**
   - **Tipo de Documento:** Seleccionar "Nota de Crédito"
   - **Serie:** Ingresar la serie deseada (ej: FC001, NC001, etc.)
   - **Numeración Actual:** Establecer el número inicial (generalmente 1)
   - **Activo:** Marcar como activo
   - **Descripción:** Agregar una descripción opcional

3. **Guardar la configuración**
   - Hacer clic en "Crear" para guardar la serie

### Ejemplo de Configuración

```
Tipo de Documento: Nota de Crédito
Serie: FC001
Numeración Actual: 1
Activo: ✓ Sí
Descripción: Serie principal para notas de crédito
```

## Serie por Defecto

El sistema incluye una serie por defecto que se crea automáticamente:

- **Tipo:** credit_note
- **Serie:** FC001
- **Número inicial:** 1
- **Estado:** Activo
- **Descripción:** Serie por defecto para Notas de Crédito

## Verificación de la Configuración

### Comprobar Series Existentes

1. **Desde el Panel de Administración:**
   - Ve a `http://restaurante.test/admin/document-series`
   - Busca series con tipo "Nota de Crédito"
   - Verifica que al menos una esté marcada como "Activa"

2. **Desde la Base de Datos:**
   ```sql
   SELECT * FROM document_series 
   WHERE document_type = 'credit_note' 
   AND active = 1;
   ```

### Probar la Generación de Notas de Crédito

1. **Crear una nota de crédito de prueba:**
   - Ve a una factura existente
   - Usa la acción "Crear Nota de Crédito"
   - Verifica que se genere con la serie configurada

2. **Verificar la numeración:**
   - La primera nota debe tener el número configurado
   - Las siguientes deben incrementar automáticamente

## Gestión de Múltiples Series

### Configurar Series Adicionales

Puedes crear múltiples series para diferentes propósitos:

```
Serie FC001: Para anulaciones generales
Serie FC002: Para devoluciones
Serie FC003: Para correcciones
```

### Activar/Desactivar Series

- Solo las series **activas** se usan para generar notas de crédito
- Si hay múltiples series activas, el sistema usa la primera encontrada
- Puedes desactivar series temporalmente sin eliminarlas

## Troubleshooting

### Error: "No hay series activas configuradas para notas de crédito"

**Causa:** No existe ninguna serie activa para el tipo 'credit_note'

**Solución:**
1. Ve a `http://restaurante.test/admin/document-series/create`
2. Crea una nueva serie con tipo "Nota de Crédito"
3. Asegúrate de marcarla como "Activa"

### Error: Numeración duplicada

**Causa:** Conflicto en la numeración automática

**Solución:**
1. Ve a la serie problemática en el panel de administración
2. Ajusta el campo "Numeración Actual" a un número mayor
3. El sistema continuará desde ese número

### Serie no aparece en el formulario

**Causa:** El tipo 'credit_note' no está en las opciones

**Solución:**
1. Verifica que el código esté actualizado
2. Limpia la caché: `php artisan cache:clear`
3. Recarga la página del formulario

## Comandos Útiles

### Verificar configuración actual
```bash
php artisan tinker
>>> \App\Models\DocumentSeries::where('document_type', 'credit_note')->get();
```

### Crear serie manualmente (si es necesario)
```bash
php artisan tinker
>>> \App\Models\DocumentSeries::create([
...     'document_type' => 'credit_note',
...     'series' => 'FC001',
...     'current_number' => 1,
...     'active' => true,
...     'description' => 'Serie para notas de crédito'
... ]);
```

### Verificar próximo número
```bash
php artisan tinker
>>> $series = \App\Models\DocumentSeries::where('document_type', 'credit_note')->where('active', true)->first();
>>> $series->getNextNumber();
```

## Integración con SUNAT

Las series configuradas se integran automáticamente con el servicio SUNAT:

1. **Generación automática:** El sistema usa la serie activa para generar el XML
2. **Numeración secuencial:** Cada nota de crédito incrementa automáticamente
3. **Validación:** SUNAT valida que la serie y numeración sean correctas

## Recomendaciones

1. **Usar series descriptivas:** FC001 para facturas, NC001 para notas, etc.
2. **Mantener una sola serie activa:** Para evitar confusiones
3. **Backup regular:** Respaldar la configuración de series
4. **Monitoreo:** Revisar periódicamente la numeración
5. **Documentación:** Mantener registro de los cambios en series

## Archivos Relacionados

- **Modelo:** `app/Models/DocumentSeries.php`
- **Resource:** `app/Filament/Resources/DocumentSeriesResource.php`
- **Servicio:** `app/Services/SunatService.php`
- **Migración:** `database/migrations/2025_01_10_000000_add_default_credit_note_series.php`

---

**Nota:** Después de configurar las series, las notas de crédito se generarán automáticamente con la numeración correcta al usar las funciones del sistema.