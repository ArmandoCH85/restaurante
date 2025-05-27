# TailAdmin Theme para Filament PHP 3.x

Este documento contiene las instrucciones para activar y configurar el tema TailAdmin en tu proyecto Filament PHP 3.x.

## 📋 Requisitos

- Laravel 10+
- Filament PHP 3.x
- Node.js y npm
- Tailwind CSS

## 🚀 Instalación y Activación

### 1. Verificar Archivos Implementados

Asegúrate de que los siguientes archivos estén en su lugar:

```
├── app/Providers/TailAdminServiceProvider.php
├── bootstrap/providers.php (actualizado)
├── tailwind.config.js (actualizado)
├── resources/css/app.css (actualizado)
└── public/images/tailadmin-logo.svg
```

### 2. Compilar Assets

```bash
# Compilar assets para desarrollo
npm run dev

# O compilar para producción
npm run build
```

### 3. Limpiar Caché de Laravel

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:clear
```

## 🎨 Características Implementadas

### Paleta de Colores TailAdmin

- **Primario**: `#3C50E0` - Botones principales, enlaces activos
- **Secundario**: `#7CD4FD` - Badges, hover de botones secundarios  
- **Fondo**: `#F2F7FF` - Wrapper del contenido general
- **Sidebar**: `#1E293B` - Fondo del sidebar
- **Texto Principal**: `#1F2937` - Títulos y textos de alto contraste
- **Texto Secundario**: `#6B7280` - Subtítulos y metadatos

### Estructura Visual

#### Sidebar (256px ancho fijo)
- ✅ Logo TailAdmin (120×32 px) en la cabecera
- ✅ Navegación con iconos 18px + labels
- ✅ Padding vertical 10px, gap 8px
- ✅ Estado activo: fondo primario, texto blanco
- ✅ Hover: fondo sidebar hover
- ✅ Separadores finos antes de submenús
- ✅ Footer con enlaces externos

#### Header (64px altura, fondo blanco)
- ✅ Título dinámico de la vista (lado izquierdo)
- ✅ Iconos de notificación + avatar (lado derecho, manejado por Filament)
- ✅ Sombra sutil (box-shadow: 0 1px 2px rgba(0,0,0,0.01))
- ✅ Botón hamburger en responsive

#### Content Wrapper
- ✅ Padding 24px, fondo general claro
- ✅ Tarjetas: bg blanco, border gris claro, radius 8px
- ✅ Sombra sutil en tarjetas
- ✅ Spacing vertical 20px entre tarjetas

#### Footer
- ✅ Texto centrado 12px gris secundario
- ✅ Fondo blanco, borde superior

### Tipografía Inter
- ✅ Familia "Inter" en todos los pesos (100-900)
- ✅ Texto base: 15px
- ✅ Títulos H1: 24px / 700
- ✅ Títulos H2: 20px / 600  
- ✅ Navegación: 14px / 500

### Responsive Design
- ✅ En ≤ 1024px el sidebar colapsa (width 0)
- ✅ Aparece ícono hamburger en el header
- ✅ Sidebar se abre como drawer
- ✅ Transiciones suaves de 150ms

## 🔧 Configuración Avanzada

### Personalizar Colores

Edita las variables CSS en `resources/css/app.css`:

```css
:root {
    --tailadmin-primary: #3C50E0;
    --tailadmin-secondary: #7CD4FD;
    --tailadmin-background: #F2F7FF;
    /* ... más variables */
}
```

### Personalizar Logo

Reemplaza `public/images/tailadmin-logo.svg` con tu propio logo manteniendo las dimensiones 120×32 px.

### Modo Oscuro

El tema incluye soporte para modo oscuro. Las variables se ajustan automáticamente:

```css
.dark {
    --tailadmin-background: #111827;
    --tailadmin-text-primary: #F9FAFB;
    --tailadmin-text-secondary: #D1D5DB;
    --tailadmin-sidebar: #1F2937;
}
```

## 🧪 Testing Responsive

### Desktop (≥ 1025px)
- Sidebar visible (256px)
- Sin botón hamburger
- Navegación completa

### Tablet/Mobile (≤ 1024px)  
- Sidebar oculto (translateX(-100%))
- Botón hamburger visible
- Sidebar como drawer al hacer clic

### Comandos de Testing

```bash
# Verificar que no hay errores de sintaxis
php artisan config:cache

# Verificar que los assets se compilan correctamente
npm run build

# Verificar que el ServiceProvider se carga
php artisan route:list
```

## 📱 Render Hooks Implementados

| Hook | Función | Implementado |
|------|---------|--------------|
| `SIDEBAR_NAV_START` | Logo + nombre | ✅ |
| `SIDEBAR_NAV_END` | Footer/enlaces externos | ✅ |
| `TOPBAR_START` | Título dinámico + hamburger | ✅ |
| `TOPBAR_END` | Notificaciones + avatar | ⚠️ Manejado por Filament |
| `CONTENT_START` | Breadcrumb + headline | ✅ |
| `CONTENT_END` | Action buttons globales | ✅ |
| `FOOTER` | Firma legal | ✅ |
| `BODY_START` | Scripts adicionales | ✅ |
| `STYLES_AFTER` | CSS personalizado | ✅ |

## 🐛 Troubleshooting

### El tema no se aplica
1. Verificar que `TailAdminServiceProvider` está registrado en `bootstrap/providers.php`
2. Limpiar caché: `php artisan optimize:clear`
3. Recompilar assets: `npm run build`

### Sidebar no responde en mobile
1. Verificar que JavaScript se carga correctamente
2. Comprobar consola del navegador por errores
3. Verificar que `toggleSidebar()` está definida

### Colores no coinciden
1. Verificar variables CSS en `resources/css/app.css`
2. Comprobar que Tailwind config incluye los colores personalizados
3. Recompilar assets después de cambios

## 📚 Referencias

- [Documentación TailAdmin](https://tailadmin.com)
- [Documentación Filament PHP](https://filamentphp.com)
- [Render Hooks Filament](https://filamentphp.com/docs/3.x/panels/render-hooks)
- [Tailwind CSS](https://tailwindcss.com)

---

**Versión**: 1.0.0  
**Compatibilidad**: Filament PHP 3.x  
**Última actualización**: $(date)
