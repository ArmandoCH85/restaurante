# Personalización de Qwen Code

Este archivo contiene configuraciones e instrucciones personalizadas para optimizar las interacciones con Qwen Code en el contexto de este proyecto específico.

## Principios fundamentales

NUNCA crees datos mock o componentes simplificados a menos que te lo digan explícitamente.
NUNCA reemplaces componentes complejos existentes con versiones simplificadas - siempre arregla el problema de verdad.
SIEMPRE trabaja con la base de código existente - no crees alternativas simplificadas nuevas.
SIEMPRE encuentra y arregla la raíz del problema en vez de crear soluciones alternativas.
Cuando debuguees problemas, enfócate en arreglar la implementación existente, no en reemplazarla.
Cuando algo no funciona, debuggea y arréglalo - no empieces de cero con una versión simple.
SIEMPRE chequea la DOC de LARAVEL en la cual el proyecto funciona

PROCESO: Arreglar el problema solicitado → Explicar posibles mejoras → Esperar aprobación explícita → Solo entonces implementar si es aprobado.

## Preferencias de comunicación

- **Idioma**: Español es el idioma preferido para todas las interacciones.
- **Estilo**: Conciso y directo, evitando explicaciones innecesarias.
- **Formato de código**: Seguir las convenciones del proyecto existente.

## Convenciones del proyecto

- **Framework**: Laravel (basado en la presencia de archivos como `artisan` y `composer.json`)
- **Herramientas**: Composer para dependencias de PHP
- **Estilo de código**: PSR-12 para PHP

## Comandos frecuentes

```bash
# Instalar dependencias
composer install

# Ejecutar migraciones
php artisan migrate

# Ejecutar servidor de desarrollo
php artisan serve
```

## Flujo de trabajo preferido

1. Antes de hacer cambios, analizar el código existente
2. Seguir las convenciones establecidas en el proyecto
3. Escribir código limpio y bien documentado
4. Verificar que los cambios no introduzcan errores
5. Probar los cambios antes de confirmarlos

## Guías para el Análisis de Código

### Manejo de Grandes Bases de Código

Reconocer limitaciones: Siempre indicar explícitamente cuando los archivos sean demasiado grandes para leer completamente. Nunca fingir haber leído todo el archivo si no fue posible.

Enfoque sistemático de búsqueda: Al buscar en el código:
- Siempre usar búsquedas insensibles a mayúsculas primero (-i con grep).
- Usar múltiples patrones de búsqueda para validar hallazgos.
- Informar sobre la metodología de búsqueda utilizada.
- Informar el recuento exacto de coincidencias encontradas.

Al analizar relaciones entre modelos:
- Buscar específicamente ForeignKey, ManyToMany y relaciones OneToOne.
- Usar patrones como grep -i "fieldname.*= models\.ForeignKey".
- Buscar tanto relaciones directas como relaciones inversas.

Protocolo de verificación:
- Después de los hallazgos iniciales, siempre realizar al menos una búsqueda de verificación.
- Informar tanto hallazgos positivos como negativos.
- Si no estás seguro, indicar claramente "No estoy seguro" en lugar de adivinar.

### Evitar Alucinaciones

Respuestas basadas en evidencia únicamente: Nunca afirmar que un modelo tiene campos o relaciones sin evidencia directa del código.

Seguimiento claro de fuentes: Siempre citar números de línea y rutas de archivos para cualquier declaración sobre la estructura del código.

Limitaciones de consulta: Indicar qué no se pudo verificar y qué búsquedas podrían ser necesarias para tener confianza completa.

Niveles de confianza: Usar indicadores explícitos de confianza:
- "Confirmado" (cuando se observa directamente en el código).
- "Probable" (cuando se infiere de fuertes evidencias).
- "Posible" (cuando lo sugieren evidencias parciales).
- "Desconocido" (cuando no se encontró ninguna evidencia).

## Personalización de respuestas

- Priorizar soluciones simples y efectivas
- Explicar el "por qué" de las decisiones técnicas
- Ofrecer alternativas cuando sea relevante
- Ser proactivo en la identificación de posibles problemas

## Manejo de errores

- Reportar errores de forma clara y concisa
- Sugerir soluciones específicas cuando sea posible
- Indicar cómo evitar problemas similares en el futuro