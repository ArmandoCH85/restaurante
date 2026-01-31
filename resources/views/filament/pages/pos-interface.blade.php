<x-filament-panels::page>
    @php
        // Forzar UTF-8 para caracteres especiales
        header('Content-Type: text/html; charset=UTF-8');
    @endphp
    <style>
        /* ========================================= */
        /* SISTEMA POS OPTIMIZADO - DISEÑO MODERNO */
        /* ========================================= */

        /* VARIABLES GLOBALES RESPONSIVAS */
        :root {
            --pos-cart-width: clamp(360px, 35vw, 450px);
            --pos-sidebar-width: clamp(160px, 20vw, 200px);
            --pos-border-radius: 6px;
            /* Sistema de sombras profesional */
            --pos-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --pos-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --pos-shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --pos-shadow-hover: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            /* Paleta profesional inspirada en Square POS */
            --pos-primary: #6366f1;        /* Indigo vibrante */
            --pos-secondary: #8b5cf6;      /* Púrpura elegante */
            --pos-success: #10b981;        /* Verde esmeralda */
            --pos-warning: #f59e0b;        /* Ámbar cálido */
            --pos-danger: #ef4444;         /* Rojo coral */
            --pos-surface: #ffffff;        /* Blanco puro */
            --pos-background: #f8fafc;     /* Gris muy claro */
            --pos-gray-50: #f8fafc;
            --pos-gray-100: #f1f5f9;
            --pos-gray-200: #e2e8f0;       /* Gris más definido */
            --pos-gray-300: #cbd5e1;       /* Gris medio */
            --pos-gray-400: #94a3b8;       /* Gris para iconos */
            --pos-gray-600: #475569;       /* Gris oscuro */
            --pos-border-subtle: #e2e8f0;  /* Bordes más suaves */
            --pos-border-focus: #6366f1;   /* Focus indigo */

            /* Variables responsivas adicionales */
            --pos-product-min-width: 130px;
            --pos-product-max-width: 180px;
            --pos-gap: clamp(4px, 0.5vw, 8px);

            /* Transiciones profesionales */
            --pos-transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --pos-transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* RESET Y BASE */
        .pos-interface * {
            box-sizing: border-box;
        }

        /* LAYOUT PRINCIPAL RESPONSIVO */
        .pos-main-container {
            display: grid;
            grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
            grid-template-areas: "sidebar products cart";
            height: calc(100vh - 120px);
            min-height: 500px;
            max-height: calc(100vh - 120px);
            overflow: hidden;
            gap: var(--pos-gap);
            background: var(--pos-gray-50);
            width: 100%;
            max-width: 100vw;
            margin: 0;
            padding: var(--pos-gap);
            box-sizing: border-box;
        }

        /* SIDEBAR CATEGORÍAS - DISEÑO GESTALT APLICADO */
        .pos-categories {
            grid-area: sidebar;
            /* Principio de Figura-Fondo: fondo elegante que no compite */
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
            border: 2px solid transparent;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* Principio de Cierre: forma completa y definida */
            box-shadow:
                0 4px 12px rgba(0, 0, 0, 0.08),
                0 2px 6px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            /* Principio de Simetría: estructura equilibrada */
            position: relative;
        }

        /* Efecto de profundidad - Principio de Figura-Fondo */
        .pos-categories::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--pos-primary) 0%, var(--pos-secondary) 100%);
            border-radius: 14px 14px 0 0;
        }

        .pos-categories-header {
            /* Principio de Proximidad: agrupado con el contenido */
            padding: 16px 12px 12px;
            /* Principio de Similitud: coherencia con el buscador */
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--pos-border-subtle);
            border-radius: 10px;
            margin: 8px 8px 6px 8px;
            /* Principio de Cierre: contenedor definido */
            box-shadow:
                0 2px 6px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            /* Principio de Simetría: centrado perfecto */
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Título con mejor tipografía - Principio de Similitud */
        .pos-categories-header h3 {
            font-size: clamp(12px, 2vw, 14px);
            font-weight: 700;
            color: #374151;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
            /* Principio de Figura-Fondo: texto destacado */
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        /* Decoración sutil del título */
        .pos-categories-header h3::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 2px;
            background: linear-gradient(90deg, var(--pos-primary) 0%, var(--pos-secondary) 100%);
            border-radius: 1px;
        }

        /* BOTÓN TOGGLE FIJO DE CATEGORÍAS */
        .pos-categories-toggle-btn-fixed {
            position: fixed;
            top: 70px;
            left: 10px;
            width: 40px;
            height: 40px;
            padding: 8px;
            background: var(--pos-primary);
            color: white;
            border: none;
            border-radius: var(--pos-border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--pos-transition);
            z-index: 1000;
            box-shadow: var(--pos-shadow);
        }

        .pos-categories-toggle-btn-fixed:hover {
            background: var(--pos-border-focus);
            transform: translateY(-1px);
            box-shadow: var(--pos-shadow-hover);
        }

        .pos-categories-toggle-icon {
            width: 20px;
            height: 20px;
        }

        /* ANIMACIÓN DE DESLIZAMIENTO HORIZONTAL DE CATEGORÍAS */
        .pos-categories {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(0); /* Estado normal - visible */
        }

        .pos-categories.collapsed {
            transform: translateX(-100%); /* Deslizar hacia la izquierda - oculto */
        }

        /* EXPANSIÓN DEL ÁREA DE PRODUCTOS CUANDO CATEGORÍAS ESTÁ COLAPSADA */
        .pos-main-container {
            transition: grid-template-columns 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .pos-main-container.categories-collapsed {
            grid-template-columns: 0 1fr var(--pos-cart-width); /* Sin espacio para sidebar */
        }

        .pos-categories-content {
            flex: 1;
            overflow-y: auto;
            /* Principio de Proximidad: padding coherente */
            padding: 6px 8px 12px;
            /* Principio de Continuidad: scroll suave */
            scroll-behavior: smooth;
        }

        /* SISTEMA DE COLORES PROFESIONALES PARA CATEGORÍAS */
        :root {
            /* Paleta de colores sutiles y profesionales */
            --cat-sopas: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);           /* Amarillo cálido suave */
            --cat-ensaladas: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);      /* Verde menta suave */
            --cat-piqueos: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);         /* Rosa coral suave */
            --cat-pastas: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);         /* Rojo salmon suave */
            --cat-entradas: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);       /* Azul lavanda suave */
            --cat-parrillas: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);      /* Naranja terracota suave */
            --cat-pollos: linear-gradient(135deg, #fde2e2 0%, #fecaca 100%);         /* Rojo cereza suave */
            --cat-platos: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);         /* Púrpura lavanda suave */
            --cat-bebidas: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);         /* Azul cielo suave */
            --cat-adicionales: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);   /* Gris plateado suave */
            --cat-carta: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);           /* Cyan glacial suave */

            /* Colores para subcategorías de bebidas - mismo tono azul pero variaciones */
            --subcat-bebidas-gaseosas: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);   /* Azul cielo más claro */
            --subcat-bebidas-naturales: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);   /* Azul cielo estándar */
            --subcat-bebidas-alcoholicas: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%); /* Azul cielo medio */
            --subcat-bebidas-calientes: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 100%);   /* Azul cielo más intenso */
            --subcat-bebidas-frozen: linear-gradient(135deg, #a5f3fc 0%, #67e8f9 100%);     /* Cyan hielo */
            --subcat-bebidas-vinos: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);      /* Púrpura vino */
            --subcat-bebidas-sangrias: linear-gradient(135deg, #ec4899 0%, #f97316 100%);   /* Rosa-naranja sangría */
            --subcat-bebidas-tragos: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);     /* Ámbar cóctel */

            /* Colores hover sutiles */
            --cat-sopas-hover: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
            --cat-ensaladas-hover: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            --cat-piqueos-hover: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            --cat-pastas-hover: linear-gradient(135deg, #feb2b2 0%, #f87171 100%);
            --cat-entradas-hover: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%);
            --cat-parrillas-hover: linear-gradient(135deg, #fdba74 0%, #fb923c 100%);
            --cat-pollos-hover: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            --cat-platos-hover: linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%);
            --cat-bebidas-hover: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            --cat-adicionales-hover: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            --cat-carta-hover: linear-gradient(135deg, #cffafe 0%, #7dd3fc 100%);
        }

        /* BOTONES DE CATEGORÍA - DISEÑO GESTALT APLICADO */
        .pos-category-btn {
            width: 100%;
            /* Principio de Proximidad: espaciado consistente */
            padding: 10px 12px;
            margin-bottom: 4px;
            text-align: center;
            /* Principio de Similitud: coherencia con otros botones */
            border: 2px solid var(--pos-border-subtle);
            border-radius: 10px;
            /* Principio de Figura-Fondo: fondo que destaca contenido */
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            color: #475569;
            font-weight: 600;
            font-size: clamp(11px, 1.8vw, 13px);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            /* Principio de Continuidad: transiciones suaves */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            /* Principio de Cierre: sombra que define límites */
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            position: relative;
            overflow: hidden;
        }

        /* Efecto de brillo sutil - Principio de Figura-Fondo */
        .pos-category-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            transition: left 0.5s ease;
        }

        .pos-category-btn:hover {
            /* Principio de Figura-Fondo: feedback visual claro */
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-color: var(--pos-primary);
            transform: translateY(-2px);
            box-shadow:
                0 4px 12px rgba(99, 102, 241, 0.15),
                0 2px 6px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            color: var(--pos-primary);
        }

        .pos-category-btn:hover::before {
            left: 100%;
        }

        .pos-category-btn.active {
            /* Principio de Figura-Fondo: estado activo destacado */
            background: linear-gradient(135deg, var(--pos-primary) 0%, var(--pos-secondary) 100%);
            color: white;
            border: 2px solid var(--pos-primary);
            transform: translateY(-1px);
            box-shadow:
                0 6px 16px rgba(99, 102, 241, 0.3),
                0 3px 8px rgba(99, 102, 241, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            /* Principio de Similitud: tipografía consistente pero destacada */
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .pos-category-btn.active::before {
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
        }

        .pos-category-btn:active {
            transform: translateY(0);
        }

        /* COLORES ESPECÍFICOS PARA CADA CATEGORÍA */
        .pos-category-btn.cat-sopas {
            background: var(--cat-sopas);
            border-color: #fde68a;
            color: #92400e;
        }

        .pos-category-btn.cat-sopas:hover {
            background: var(--cat-sopas-hover);
            border-color: #fcd34d;
        }

        .pos-category-btn.cat-sopas.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border-color: #d97706;
        }

        .pos-category-btn.cat-ensaladas {
            background: var(--cat-ensaladas);
            border-color: #a7f3d0;
            color: #065f46;
        }

        .pos-category-btn.cat-ensaladas:hover {
            background: var(--cat-ensaladas-hover);
            border-color: #6ee7b7;
        }

        .pos-category-btn.cat-ensaladas.active {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border-color: #047857;
        }

        .pos-category-btn.cat-piqueos {
            background: var(--cat-piqueos);
            border-color: #fecaca;
            color: #991b1b;
        }

        .pos-category-btn.cat-piqueos:hover {
            background: var(--cat-piqueos-hover);
            border-color: #fca5a5;
        }

        .pos-category-btn.cat-piqueos.active {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border-color: #b91c1c;
        }

        .pos-category-btn.cat-pastas {
            background: var(--cat-pastas);
            border-color: #feb2b2;
            color: #991b1b;
        }

        .pos-category-btn.cat-pastas:hover {
            background: var(--cat-pastas-hover);
            border-color: #f87171;
        }

        .pos-category-btn.cat-pastas.active {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border-color: #b91c1c;
        }

        .pos-category-btn.cat-entradas {
            background: var(--cat-entradas);
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .pos-category-btn.cat-entradas:hover {
            background: var(--cat-entradas-hover);
            border-color: #a5b4fc;
        }

        .pos-category-btn.cat-entradas.active {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-color: #4f46e5;
        }

        .pos-category-btn.cat-parrillas {
            background: var(--cat-parrillas);
            border-color: #fdba74;
            color: #9a3412;
        }

        .pos-category-btn.cat-parrillas:hover {
            background: var(--cat-parrillas-hover);
            border-color: #fb923c;
        }

        .pos-category-btn.cat-parrillas.active {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: white;
            border-color: #c2410c;
        }

        .pos-category-btn.cat-pollos {
            background: var(--cat-pollos);
            border-color: #fecaca;
            color: #991b1b;
        }

        .pos-category-btn.cat-pollos:hover {
            background: var(--cat-pollos-hover);
            border-color: #fca5a5;
        }

        .pos-category-btn.cat-pollos.active {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border-color: #b91c1c;
        }

        .pos-category-btn.cat-platos {
            background: var(--cat-platos);
            border-color: #e9d5ff;
            color: #581c87;
        }

        .pos-category-btn.cat-platos:hover {
            background: var(--cat-platos-hover);
            border-color: #d8b4fe;
        }

        .pos-category-btn.cat-platos.active {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border-color: #7c3aed;
        }

        .pos-category-btn.cat-bebidas {
            background: var(--cat-bebidas);
            border-color: #bfdbfe;
            color: #1e40af;
        }

        .pos-category-btn.cat-bebidas:hover {
            background: var(--cat-bebidas-hover);
            border-color: #93c5fd;
        }

        .pos-category-btn.cat-bebidas.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #2563eb;
        }

        .pos-category-btn.cat-adicionales {
            background: var(--cat-adicionales);
            border-color: #e2e8f0;
            color: #334155;
        }

        .pos-category-btn.cat-adicionales:hover {
            background: var(--cat-adicionales-hover);
            border-color: #cbd5e1;
        }

        .pos-category-btn.cat-adicionales.active {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            border-color: #475569;
        }

        .pos-category-btn.cat-carta {
            background: var(--cat-carta);
            border-color: #cffafe;
            color: #0e7490;
        }

        .pos-category-btn.cat-carta:hover {
            background: var(--cat-carta-hover);
            border-color: #7dd3fc;
        }

        .pos-category-btn.cat-carta.active {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            border-color: #0891b2;
        }

        .pos-category-btn.cat-default {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-color: var(--pos-border-subtle);
            color: #475569;
        }

        .pos-category-btn.cat-default:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-color: var(--pos-primary);
        }

        .pos-category-btn.cat-default.active {
            background: linear-gradient(135deg, var(--pos-primary) 0%, var(--pos-secondary) 100%);
            color: white;
            border-color: var(--pos-primary);
        }

        /* COLORES AUTO-GENERADOS PARA CATEGORÍAS NUEVAS */
        .pos-category-btn[class*="cat-auto-"] {
            background: var(--auto-bg);
            border-color: var(--auto-border);
            color: var(--auto-text);
        }

        .pos-category-btn[class*="cat-auto-"]:hover {
            background: var(--auto-bg-hover);
            border-color: var(--auto-border-hover);
        }

        .pos-category-btn[class*="cat-auto-"].active {
            background: var(--auto-bg-active);
            color: var(--auto-text-active);
            border-color: var(--auto-border-active);
        }

        /* SUBCATEGORÍAS - DISEÑO JERÁRQUICO CON PRINCIPIOS GESTALT */
        .pos-subcategory-btn {
            /* Principio de Similitud: base similar pero diferenciada */
            width: 100%;
            /* Principio de Jerarquía: más pequeño que categoría principal */
            padding: 6px 8px;
            margin-bottom: 2px;
            text-align: center;
            /* Principio de Figura-Fondo: color diferenciado para jerarquía */
            border: 1px solid rgba(156, 163, 175, 0.3);
            border-radius: 6px;
            /* Principio de Similitud: pero con variación jerárquica */
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            color: #6b7280;
            font-weight: 500;
            font-size: clamp(9px, 1.4vw, 11px);
            text-transform: uppercase;
            letter-spacing: 0.2px;
            /* Principio de Continuidad: transiciones más sutiles */
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            /* Principio de Proximidad: sombra más sutil */
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
            /* Principio de Jerarquía: menos prominente */
            opacity: 0.9;
        }

        /* Efecto de brillo para subcategorías - más sutil */
        .pos-subcategory-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
            transition: left 0.4s ease;
        }

        .pos-subcategory-btn:hover {
            /* Principio de Figura-Fondo: feedback más sutil que categorías principales */
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-1px);
            box-shadow:
                0 2px 6px rgba(99, 102, 241, 0.1),
                0 1px 3px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
            color: #4b5563;
            opacity: 1;
        }

        .pos-subcategory-btn:hover::before {
            left: 100%;
        }

        .pos-subcategory-btn.active {
            /* Principio de Figura-Fondo: activo pero secundario */
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            border: 1px solid #6366f1;
            transform: translateY(-1px);
            box-shadow:
                0 3px 8px rgba(99, 102, 241, 0.2),
                0 2px 4px rgba(99, 102, 241, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            /* Principio de Jerarquía: menos peso visual que principal */
            font-weight: 600;
            letter-spacing: 0.3px;
            opacity: 1;
        }

        .pos-subcategory-btn.active::before {
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.15) 50%, transparent 100%);
        }

        .pos-subcategory-btn:active {
            transform: translateY(0);
            transition: transform 0.1s ease;
        }

        /* CLASES ESPECÍFICAS PARA SUBCATEGORÍAS DE BEBIDAS */
        .pos-subcategory-btn.subcat-gaseosas {
            background: var(--subcat-bebidas-gaseosas);
            border-color: #bae6fd;
            color: #0c4a6e;
        }

        .pos-subcategory-btn.subcat-gaseosas:hover {
            background: linear-gradient(135deg, #bae6fd 0%, #7dd3fc 100%);
            border-color: #7dd3fc;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-gaseosas.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border-color: #0284c7;
        }

        .pos-subcategory-btn.subcat-naturales {
            background: var(--subcat-bebidas-naturales);
            border-color: #bfdbfe;
            color: #1e40af;
        }

        .pos-subcategory-btn.subcat-naturales:hover {
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            border-color: #93c5fd;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-naturales.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #2563eb;
        }

        .pos-subcategory-btn.subcat-alcoholicas {
            background: var(--subcat-bebidas-alcoholicas);
            border-color: #93c5fd;
            color: #1d4ed8;
        }

        .pos-subcategory-btn.subcat-alcoholicas:hover {
            background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%);
            border-color: #60a5fa;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-alcoholicas.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-color: #1d4ed8;
        }

        .pos-subcategory-btn.subcat-calientes {
            background: var(--subcat-bebidas-calientes);
            border-color: #38bdf8;
            color: #0c4a6e;
        }

        .pos-subcategory-btn.subcat-calientes:hover {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            border-color: #0ea5e9;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-calientes.active {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            border-color: #0369a1;
        }

        .pos-subcategory-btn.subcat-frozen {
            background: var(--subcat-bebidas-frozen);
            border-color: #67e8f9;
            color: #0c4a6e;
        }

        .pos-subcategory-btn.subcat-frozen:hover {
            background: linear-gradient(135deg, #67e8f9 0%, #22d3ee 100%);
            border-color: #22d3ee;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-frozen.active {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            color: white;
            border-color: #0e7490;
        }

        .pos-subcategory-btn.subcat-vinos {
            background: var(--subcat-bebidas-vinos);
            border-color: #8b5cf6;
            color: #1e1b4b;
        }

        .pos-subcategory-btn.subcat-vinos:hover {
            background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
            border-color: #a855f7;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-vinos.active {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white;
            border-color: #6d28d9;
        }

        .pos-subcategory-btn.subcat-sangrias {
            background: var(--subcat-bebidas-sangrias);
            border-color: #f97316;
            color: #9a3412;
        }

        .pos-subcategory-btn.subcat-sangrias:hover {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-color: #ea580c;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-sangrias.active {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border-color: #b91c1c;
        }

        .pos-subcategory-btn.subcat-tragos {
            background: var(--subcat-bebidas-tragos);
            border-color: #d97706;
            color: #92400e;
        }

        .pos-subcategory-btn.subcat-tragos:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            border-color: #b45309;
            transform: translateY(-1px);
        }

        .pos-subcategory-btn.subcat-tragos.active {
            background: linear-gradient(135deg, #a16207 0%, #78350f 100%);
            color: white;
            border-color: #78350f;
        }

        /* Contenedor de subcategorías - Principio de Proximidad */
        .pos-subcategories-container {
            /* Principio de Proximidad: agrupado visualmente */
            border-top: 1px solid var(--pos-border-subtle);
            margin-top: 6px;
            padding-top: 8px;
            position: relative;
        }

        .pos-subcategories-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20%;
            right: 20%;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--pos-primary) 50%, transparent 100%);
        }

        /* Título de subcategorías - Principio de Jerarquía */
        .pos-subcategories-title {
            font-size: 10px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            text-align: center;
            /* Principio de Figura-Fondo: menos prominente */
            opacity: 0.8;
        }






        /* ÁREA DE PRODUCTOS RESPONSIVA */
        .pos-products-area {
            grid-area: products;
            background: white;
            display: flex;
            flex-direction: column;
            border-radius: var(--pos-border-radius);
            box-shadow: var(--pos-shadow);
            overflow: hidden;
            min-width: 0; /* Permite que el grid se contraiga */
        }

        /* BARRA DE BÚSQUEDA - DISEÑO GESTALT APLICADO */
        .pos-search-bar {
            padding: 12px;
            /* Principio de Figura-Fondo: fondo sutil que no compite */
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid transparent;
            border-radius: 12px;
            margin: 8px;
            position: relative;
            /* Principio de Cierre: forma completa y cerrada */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.06);
            /* Principio de Simetría: balance visual */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pos-search-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            /* Principio de Proximidad: elementos relacionados agrupados */
            gap: 0;
            /* Principio de Continuidad: flujo visual dirigido */
            background: white;
            border-radius: 10px;
            border: 2px solid var(--pos-border-subtle);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            /* Principio de Cierre: contenedor completo */
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Estado focus del contenedor - Principio de Figura-Fondo */
        .pos-search-container:focus-within {
            border-color: var(--pos-primary);
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
            box-shadow:
                inset 0 1px 3px rgba(0, 0, 0, 0.05),
                0 0 0 3px rgba(99, 102, 241, 0.1),
                0 4px 12px rgba(99, 102, 241, 0.15);
            transform: translateY(-1px);
        }

        .pos-search-icon {
            /* Principio de Proximidad: cerca del input */
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: clamp(16px, 2.2vw, 20px);
            height: clamp(16px, 2.2vw, 20px);
            /* Principio de Similitud: color coherente con el sistema */
            color: var(--pos-gray-400);
            pointer-events: none;
            z-index: 2;
            transition: all 0.3s ease;
        }

        /* Icono activo - Principio de Figura-Fondo */
        .pos-search-container:focus-within .pos-search-icon {
            color: var(--pos-primary);
            transform: translateY(-50%) scale(1.1);
        }

        .pos-search-input {
            width: 100%;
            /* Principio de Proximidad: padding que respeta el icono */
            padding: clamp(12px, 2vw, 16px) clamp(45px, 5vw, 55px) clamp(12px, 2vw, 16px) clamp(45px, 5vw, 55px);
            border: none;
            border-radius: 0;
            font-size: clamp(13px, 2.2vw, 16px);
            font-weight: 500;
            /* Principio de Continuidad: transición suave */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            /* Principio de Similitud: tipografía coherente */
            font-family: inherit;
            letter-spacing: 0.3px;
            color: #1f2937;
            /* Mejorar en móviles */
            -webkit-appearance: none;
            appearance: none;
            touch-action: manipulation;
        }

        .pos-search-input:focus {
            outline: none;
            /* Principio de Figura-Fondo: sin competir con el contenedor */
            background: transparent;
            color: #111827;
        }

        .pos-search-input::placeholder {
            /* Principio de Similitud: color coherente pero sutil */
            color: var(--pos-gray-400);
            font-weight: 400;
            font-style: italic;
            transition: all 0.3s ease;
        }

        .pos-search-container:focus-within .pos-search-input::placeholder {
            color: var(--pos-gray-300);
            transform: translateX(2px);
        }

        .pos-search-clear {
            /* Principio de Proximidad: cerca del input, alineado visualmente */
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: clamp(22px, 2.8vw, 26px);
            height: clamp(22px, 2.8vw, 26px);
            border: 1px solid var(--pos-gray-300);
            /* Principio de Similitud: forma consistente con el diseño */
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--pos-gray-500);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(13px, 2vw, 16px);
            font-weight: 600;
            /* Principio de Continuidad: transición suave */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 3;
            opacity: 0;
            visibility: hidden;
            /* Principio de Cierre: forma completa */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pos-search-clear.show {
            opacity: 1;
            visibility: visible;
            /* Principio de Figura-Fondo: aparece suavemente */
            transform: translateY(-50%) scale(1);
        }

        .pos-search-clear:hover {
            /* Principio de Figura-Fondo: feedback visual claro */
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-color: #dc2626;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }

        .pos-search-clear:active {
            transform: translateY(-50%) scale(0.95);
        }

        .pos-search-loading {
            position: absolute;
            right: clamp(35px, 4vw, 45px);
            top: 50%;
            transform: translateY(-50%);
            width: clamp(14px, 2vw, 18px);
            height: clamp(14px, 2vw, 18px);
            z-index: 2;
        }

        .pos-search-spinner {
            width: 100%;
            height: 100%;
            border: 2px solid var(--pos-gray-200);
            border-top: 2px solid var(--pos-primary);
            border-radius: 50%;
            animation: pos-spin 1s linear infinite;
        }

        @keyframes pos-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* GRID DE PRODUCTOS - DISEÑO GESTALT APLICADO */
        .pos-products-grid {
            flex: 1;
            overflow-y: auto;
            /* Principio de Proximidad: padding coherente con otros componentes */
            padding: 12px;
            /* Principio de Similitud: coherencia con categorías y buscador */
            border: 2px solid var(--pos-border-subtle);
            border-radius: 12px;
            margin: 8px;
            /* Principio de Figura-Fondo: fondo elegante que realza productos */
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
            /* Principio de Cierre: contenedor bien definido */
            box-shadow:
                0 4px 12px rgba(0, 0, 0, 0.06),
                0 2px 6px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            /* Principio de Continuidad: scroll suave */
            scroll-behavior: smooth;
            position: relative;
            overflow-x: hidden;
        }

        /* Efecto de profundidad superior - Principio de Figura-Fondo */
        .pos-products-grid::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                var(--pos-success) 0%,
                var(--pos-primary) 50%,
                var(--pos-secondary) 100%);
            border-radius: 12px 12px 0 0;
            z-index: 1;
        }

        /* Efecto sutil de textura - Principio de Figura-Fondo */
        .pos-products-grid::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        /* GRID DE PRODUCTOS RESPONSIVO - DISEÑO GESTALT */
        .pos-products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            /* Principio de Proximidad: espaciado equilibrado */
            gap: clamp(8px, 1vw, 12px);
            flex: 1;
            overflow-y: auto;
            /* Principio de Simetría: padding balanceado */
            padding: clamp(8px, 1.5vw, 16px);
            padding-bottom: clamp(16px, 2vw, 24px);
            /* Principio de Continuidad: transición suave en cambios */
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        /* Efecto de entrada suave para productos - Principio de Continuidad */
        .pos-products-container .pos-product-card {
            animation: fadeInProduct 0.4s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .pos-products-container .pos-product-card:nth-child(1) { animation-delay: 0.05s; }
        .pos-products-container .pos-product-card:nth-child(2) { animation-delay: 0.1s; }
        .pos-products-container .pos-product-card:nth-child(3) { animation-delay: 0.15s; }
        .pos-products-container .pos-product-card:nth-child(4) { animation-delay: 0.2s; }
        .pos-products-container .pos-product-card:nth-child(5) { animation-delay: 0.25s; }
        .pos-products-container .pos-product-card:nth-child(6) { animation-delay: 0.3s; }

        @keyframes fadeInProduct {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CARDS DE PRODUCTOS - DISEÑO SIMPLE Y FUNCIONAL */
        .pos-product-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--pos-border-subtle);
            border-radius: 12px;
            padding: 0;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            /* Proporción optimizada para texto */
            aspect-ratio: 1 / 1.5;
            min-height: 140px;
            max-height: 200px;
            display: grid;
            /* Grid optimizado: imagen pequeña, más espacio para contenido */
            grid-template-rows: 0.7fr 1.2fr 0.5fr;
            align-content: stretch;
            /* Experiencia táctil mejorada */
            touch-action: manipulation;
            user-select: none;
            /* Sombra sutil inicial */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.06);
            /* Borde sutil */
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .pos-product-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12), 0 6px 12px rgba(0, 0, 0, 0.08);
            border: 2px solid var(--pos-primary);
            background: linear-gradient(145deg, #ffffff 0%, #f0f9ff 100%);
        }

        .pos-product-card:active {
            transform: translateY(-2px) scale(1.01);
            transition: all 0.1s ease;
        }

        /* Efecto de selección con proporción áurea */
        .pos-product-card:focus-within {
            outline: none;
            border: 2px solid var(--pos-success);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        /* Sección de imagen - Reducida para dar más espacio al texto */
        .pos-product-image {
            grid-row: 1;
            position: relative;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
            margin: 0;
            /* Altura reducida significativamente */
            max-height: 60px;
            min-height: 50px;
        }

        .pos-product-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(99, 102, 241, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .pos-product-card:hover .pos-product-image::before {
            opacity: 1;
        }

        .pos-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .pos-product-card:hover .pos-product-image img {
            transform: scale(1.1);
        }

        /* Placeholder para productos sin imagen */
        .pos-product-image .product-placeholder {
            font-size: clamp(24px, 4vw, 32px);
            font-weight: 800;
            color: var(--pos-primary);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, var(--pos-primary), var(--pos-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Sección de contenido - MÁS ESPACIO para nombre + categoría */
        .pos-product-content {
            grid-row: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px 8px;
            gap: 6px;
            position: relative;
            /* Más espacio disponible */
            min-height: 92px;
        }

        .pos-product-card .pos-product-name {
            font-size: clamp(9px, 1.2vw, 11px);
            font-weight: 600;
            text-transform: uppercase;
            color: #1f2937;
            line-height: 1.25;
            text-align: center;
            letter-spacing: 0.2px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 4px;
            padding: 4px 6px;
            word-break: break-word;
            hyphens: auto;
            margin-bottom: 3px;
            width: 100%;
            display: block;
            margin-left: auto;
            margin-right: auto;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        /* Categoría integrada con principio de similaridad */
        .pos-product-category {
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--pos-primary);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(79, 70, 229, 0.15) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 10px;
            padding: 3px 6px;
            letter-spacing: 0.1px;
            text-align: center;
            /* Centrado perfecto horizontal y vertical */
            display: block;
            margin: 0 auto;
            width: fit-content;
            max-width: 95%;
            /* Si el texto es muy largo, permitir ajuste */
            word-break: break-word;
            hyphens: auto;
            line-height: 1.2;
            /* Si necesita 2 líneas, permitirlo */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* Efectos hover para contenido integrado */
        .pos-product-card:hover .pos-product-content {
            transform: translateY(-2px);
        }

        .pos-product-card:hover .pos-product-name {
            color: var(--pos-primary);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pos-product-card:hover .pos-product-category {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(79, 70, 229, 0.2) 100%);
            border-color: rgba(99, 102, 241, 0.4);
            transform: scale(1.05);
        }

        /* Sección de precio - Proporción áurea inferior */
        .pos-product-price {
            grid-row: 3;
            font-size: clamp(12px, 2.5vw, 16px);
            font-weight: 800;
            color: var(--pos-success);
            padding: 4px 12px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
            border-radius: 0 0 12px 12px;
            /* Efecto de precio destacado */
            text-shadow: 0 1px 3px rgba(16, 185, 129, 0.2);
            letter-spacing: 0.3px;
        }

        .pos-product-price::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--pos-success) 50%, transparent 100%);
            opacity: 0.3;
        }

        /* Efecto de brillo en el precio al hover */
        .pos-product-card:hover .pos-product-price {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            transform: scale(1.05);
            text-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
        }




/* CARRITO RESPONSIVO - SOLUCIÓN COMPLETA PARA MÓVILES Y TABLETS */
.pos-cart {
    grid-area: cart;
    background: white;
    border: 1px solid var(--pos-border-subtle);
    border-radius: var(--pos-border-radius);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--pos-shadow);
    height: 100%;
    min-height: 300px; /* Altura mínima para móviles */
}

        /* HEADER DEL CARRITO - DISEÑO GESTALT APLICADO */
        .pos-cart-header {
            /* Principio de Proximidad: padding coherente con otros componentes */
            padding: 6px 8px 4px;
            /* Principio de Similitud: coherencia con categorías y buscador */
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #dbeafe 100%);
            border: 1px solid var(--pos-border-subtle);
            border-radius: 8px;
            margin: 4px;
            /* Principio de Cierre: contenedor bien definido */
            box-shadow:
                0 2px 6px rgba(59, 130, 246, 0.06),
                0 1px 3px rgba(0, 0, 0, 0.03),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            /* Principio de Simetría: estructura equilibrada */
            position: relative;
            /* Principio de Continuidad: transición suave */
            transition: all 0.2s ease;
        }

        /* Efecto de profundidad superior - Principio de Figura-Fondo */
        .pos-cart-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                var(--pos-warning) 0%,
                var(--pos-success) 50%,
                var(--pos-primary) 100%);
            border-radius: 12px 12px 0 0;
            z-index: 1;
        }

        /* Efecto sutil de textura - Principio de Figura-Fondo */
        .pos-cart-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 15% 15%, rgba(59, 130, 246, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 85% 85%, rgba(16, 185, 129, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
            border-radius: 12px;
        }

        /* Hover state para interactividad sutil */
        .pos-cart-header:hover {
            /* Principio de Figura-Fondo: feedback visual sutil */
            background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 50%, #bfdbfe 100%);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
            box-shadow:
                0 6px 16px rgba(59, 130, 246, 0.12),
                0 3px 8px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        /* TÍTULO DEL CARRITO MEJORADO - NUEVO DISEÑO */
        .pos-cart-title-new {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--pos-border-subtle);
            border-radius: 6px;
            padding: 6px 8px;
            margin-bottom: 3px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .pos-cart-title-new:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 3px 8px rgba(99, 102, 241, 0.1);
        }

        /* FILA 1: INFORMACIÓN PRINCIPAL */
        .pos-cart-main-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        /* BADGE DE MESA/VENTA MEJORADO */
        .pos-cart-mesa-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.3px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            flex: 1;
        }

        .pos-cart-mesa-badge.mesa {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 1px solid #047857;
        }

        .pos-cart-mesa-badge.venta-directa {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: 1px solid #4338ca;
        }

        .pos-cart-mesa-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .pos-cart-icon {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
        }

        .pos-cart-mesa-label {
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        /* BOTÓN LIMPIAR MEJORADO */
        .pos-cart-clear-btn-new {
            width: 24px;
            height: 24px;
            border: 1px solid #ef4444;
            border-radius: 4px;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
            margin-left: 4px;
        }

        .pos-cart-clear-btn-new:hover:not(:disabled) {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(239, 68, 68, 0.3);
        }

        .pos-cart-clear-btn-new:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pos-clear-icon {
            width: 12px;
            height: 12px;
        }

        /* FILA 2: MÉTRICAS Y CONTROLES */
        .pos-cart-metrics {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: rgba(248, 250, 252, 0.5);
            border-radius: 4px;
            padding: 4px 6px;
            border: 1px solid var(--pos-gray-200);
        }

        /* CONTADOR DE PRODUCTOS */
        .pos-cart-product-counter {
            display: flex;
            align-items: center;
            gap: 3px;
            flex: 1;
        }

        .pos-metric-icon {
            width: 10px;
            height: 10px;
            color: var(--pos-gray-500);
            flex-shrink: 0;
        }

        .pos-metric-value {
            font-size: 12px;
            font-weight: 700;
            color: var(--pos-primary);
            min-width: 16px;
            text-align: center;
        }

        .pos-metric-label {
            font-size: 7px;
            font-weight: 600;
            color: var(--pos-gray-500);
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        /* SEPARADOR VISUAL */
        .pos-cart-separator {
            width: 1px;
            height: 16px;
            background: linear-gradient(to bottom, transparent, var(--pos-gray-300), transparent);
            margin: 0 4px;
        }

        /* CONTROL DE COMENSALES */
        .pos-cart-guests-control {
            display: flex;
            align-items: center;
            gap: 3px;
            flex: 1;
        }

        .pos-guests-input {
            width: 60px; /* Aumentado de 40px */
            height: 36px; /* Aumentado de 24px */
            border: 2px solid var(--pos-primary);
            border-radius: 6px; /* Ligeramente más redondeado */
            text-align: center;
            font-weight: 800;
            font-size: 18px; /* Aumentado de 14px */
            color: #1f2937;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            transition: all 0.2s ease;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            /* Mejorar contraste */
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.3px;
            padding: 0 4px; /* Padding añadido */
        }

        .pos-guests-input:focus {
            outline: none;
            border-color: var(--pos-success);
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
            box-shadow:
                0 0 0 3px rgba(16, 185, 129, 0.2),
                0 3px 6px rgba(0, 0, 0, 0.15);
            transform: scale(1.05);
            color: #065f46;
            font-weight: 900;
        }

        .pos-guests-input::placeholder {
            color: var(--pos-gray-400);
            font-weight: 600;
            opacity: 0.8;
        }

        /* RESPONSIVE PARA MÓVILES - HEADER CARRITO */
        @media (max-width: 767px) {
            .pos-cart-header {
                padding: 4px 6px 3px;
                margin: 3px;
            }

            .pos-cart-title-new {
                padding: 4px 6px;
                gap: 4px;
                margin-bottom: 2px;
            }

            .pos-cart-mesa-badge {
                padding: 3px 6px;
                font-size: 10px;
                gap: 3px;
            }

            .pos-cart-clear-btn-new {
                width: 20px;
                height: 20px;
                margin-left: 3px;
            }

            .pos-clear-icon {
                width: 10px;
                height: 10px;
            }

            .pos-cart-icon {
                width: 10px;
                height: 10px;
            }

            .pos-cart-metrics {
                padding: 3px 4px;
            }

            .pos-metric-value {
                font-size: 11px;
                min-width: 14px;
            }

            .pos-metric-label {
                font-size: 6px;
            }

            .pos-metric-icon {
                width: 8px;
                height: 8px;
            }

            .pos-guests-input {
                width: 50px; /* Aumentado de 32px */
                height: 32px; /* Aumentado de 20px */
                font-size: 16px; /* Aumentado de 12px */
                font-weight: 800;
                border-width: 1px;
            }

            .pos-cart-separator {
                height: 14px;
                margin: 0 3px;
            }
        }

        @media (max-width: 480px) {
            .pos-cart-header {
                padding: 3px 4px 2px;
                margin: 2px;
            }

            .pos-cart-title-new {
                padding: 3px 4px;
                gap: 3px;
                margin-bottom: 1px;
            }

            .pos-cart-mesa-badge {
                padding: 2px 4px;
                font-size: 9px;
                gap: 2px;
            }

            .pos-cart-icon {
                width: 8px;
                height: 8px;
            }

            .pos-cart-clear-btn-new {
                width: 18px;
                height: 18px;
                margin-left: 2px;
            }

            .pos-clear-icon {
                width: 8px;
                height: 8px;
            }

            .pos-cart-metrics {
                padding: 2px 3px;
            }

            .pos-metric-icon {
                width: 7px;
                height: 7px;
            }

            .pos-metric-value {
                font-size: 10px;
                min-width: 12px;
            }

            .pos-metric-label {
                font-size: 5px;
            }

            .pos-guests-input {
                width: 44px; /* Aumentado de 28px */
                height: 28px; /* Aumentado de 18px */
                font-size: 14px; /* Aumentado de 11px */
                font-weight: 800;
                border-width: 1px;
            }

            .pos-cart-separator {
                height: 12px;
                margin: 0 2px;
            }
        }

        /* TÍTULO DEL CARRITO ORIGINAL - MANTENER COMPATIBILIDAD */
        .pos-cart-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 8px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-bottom: 4px;
            gap: 8px;
        }

        .pos-cart-title-left {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            flex: 1;
        }

        /* INFORMACIÓN DE MESA/VENTA - Solo badge de mesa */
        .pos-cart-mesa-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            box-shadow:
                0 4px 8px rgba(16, 185, 129, 0.2),
                0 1px 2px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .pos-cart-mesa-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .pos-cart-mesa-info.venta-directa {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow:
                0 4px 8px rgba(99, 102, 241, 0.2),
                0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .pos-cart-mesa-info:hover {
            transform: translateY(-1px);
            box-shadow:
                0 6px 12px rgba(16, 185, 129, 0.25),
                0 2px 4px rgba(0, 0, 0, 0.12);
        }

        .pos-cart-mesa-info.venta-directa:hover {
            box-shadow:
                0 6px 12px rgba(99, 102, 241, 0.25),
                0 2px 4px rgba(0, 0, 0, 0.12);
        }

        .pos-cart-mesa-info svg {
            width: 18px;
            height: 18px;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
        }


        .pos-cart-mesa-text {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* CONTADOR DE ITEMS - Badge al costado de comensales */
        .pos-cart-items-counter {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            min-width: 36px;
            height: 32px;
            text-align: center;
            box-shadow:
                0 3px 6px rgba(245, 158, 11, 0.2),
                0 1px 2px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .pos-cart-items-counter::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .pos-cart-items-counter:hover {
            transform: translateY(-1px);
            box-shadow:
                0 4px 8px rgba(245, 158, 11, 0.25),
                0 2px 4px rgba(0, 0, 0, 0.12);
        }

        /* INPUT DE COMENSALES - Diseño elegante */
        .pos-cart-guests-input {
            display: flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 6px 10px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            height: 32px;
            transition: all 0.2s ease;
        }

        .pos-cart-guests-input:hover {
            border-color: #cbd5e1;
            box-shadow:
                0 3px 6px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        .pos-cart-guests-input:focus-within {
            border-color: #6366f1;
            box-shadow:
                0 3px 6px rgba(99, 102, 241, 0.1),
                0 0 0 3px rgba(99, 102, 241, 0.05);
        }

        .pos-cart-guests-input svg {
            width: 14px;
            height: 14px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .pos-cart-guests-input input {
            width: 28px;
            min-width: 28px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            color: #111827;
            outline: none;
            padding: 0;
        }

        .pos-cart-guests-input input::placeholder {
            color: #9ca3af;
            font-weight: 500;
        }

        /* Ocultar etiqueta pax */
        .pos-cart-guests-label {
            display: none;
        }

        /* BOTÓN UNIR CUENTAS */
        .pos-quick-action-btn.btn-unir.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 2px solid #059669;
        }

        .pos-quick-action-btn.btn-unir.success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* BOTÓN LIMPIAR */
        .pos-cart-clear-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #ef4444;
            border-radius: 4px;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .pos-cart-clear-btn:hover:not(:disabled) {
            background: #fee2e2;
        }

        .pos-cart-clear-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pos-cart-clear-btn svg {
            width: 16px;
            height: 16px;
        }

/* ACCIONES RÁPIDAS - OPTIMIZACIÓN PARA MÓVILES */
.pos-quick-actions {
    margin-top: 8px;
    padding: 8px;
    background: white;
    border-radius: var(--pos-border-radius);
    border: 1px solid var(--pos-gray-200);
    position: relative;
    z-index: 10;
}

.pos-quick-actions-title {
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 6px;
    text-align: center;
}

/* GRID DE ACCIONES RÁPIDAS - DISEÑO ADAPTATIVO */
.pos-quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 3px;
}

/* ITEMS DEL CARRITO - OPTIMIZACIÓN PARA TOQUE */
.pos-cart-items {
    flex: 1;
    overflow-y: auto;
    padding: 4px 4px 2px;
    border: 1px solid var(--pos-border-subtle);
    border-radius: var(--pos-border-radius);
    margin: 4px;
    background: white;
    min-height: 150px;
    max-height: calc(100vh - 380px);
    display: flex;
    flex-direction: column;
    gap: 4px;
    --cart-item-font-size: 11.5px;
    --cart-item-font-size-sm: 10.5px;
    --cart-item-padding: 6px;
}

.pos-cart-item {
    background: var(--pos-gray-50);
    border: 1px solid var(--pos-border-subtle);
    border-radius: 6px;
    padding: var(--cart-item-padding);
    margin: 0;
    transition: background .15s ease, box-shadow .15s ease;
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-rows: auto auto auto;
    align-items: start;
    column-gap: 6px;
    row-gap: 4px;
    font-size: var(--cart-item-font-size);
    line-height: 1.25;
}

.pos-cart-item:hover {
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

.pos-cart-item-header {
    display: contents;
}

.pos-cart-item-info {
    grid-column: 1 / 2;
    grid-row: 1 / 3;
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin: 0;
}

.pos-cart-item-name {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    flex: unset;
    margin: 0 0 4px 0;
    line-height: 1.3;
    word-break: break-word;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    box-shadow:
        0 2px 4px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    letter-spacing: 0.3px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    transition: all 0.2s ease;
    min-height: 32px;
    display: flex;
    align-items: center;
}

.pos-cart-item:hover .pos-cart-item-name {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow:
        0 3px 6px rgba(0, 0, 0, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    color: #020617;
}

.pos-cart-item:focus-within .pos-cart-item-name {
    border-color: #6366f1;
    box-shadow:
        0 3px 6px rgba(99, 102, 241, 0.15),
        0 0 0 2px rgba(99, 102, 241, 0.1);
}

.pos-cart-item-price {
    font-size: var(--cart-item-font-size-sm);
    color: #059669;
    font-weight: 600;
    white-space: nowrap;
}

/* BOTÓN DE ELIMINACIÓN - TAMAÑO TÁCTIL ÓPTIMO */
.pos-item-remove-btn {
    width: clamp(32px, 4.5vw, 36px);
    height: clamp(32px, 4.5vw, 36px);
    border: none;
    border-radius: var(--pos-border-radius);
    background: var(--pos-danger);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--pos-transition);
    font-size: clamp(14px, 2.2vw, 16px);
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(239, 68, 68, 0.2);
    min-width: auto;
}

/* Overrides compactos para vista densificada del carrito */
.pos-cart-items .pos-item-remove-btn {
    width: 28px;
    height: 28px;
    min-height: 28px;
    font-size: 14px;
    border: 1px solid #fecaca;
    border-radius: 6px;
    background: #fef2f2;
    color: #dc2626;
    box-shadow: none;
    padding: 0;
    grid-column: 2 / 3;
    grid-row: 1;
}
.pos-cart-items .pos-item-remove-btn:hover:not(:disabled){ background:#fee2e2; }
.pos-cart-items .pos-item-remove-btn:active:not(:disabled){ transform: scale(.9); }



/* CONTROLES DE CANTIDAD - MEJORADOS PARA MÓVILES */
.pos-quantity-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    padding: 6px 8px;
    border-radius: 8px;
    border: 1px solid var(--pos-gray-200);
    gap: 8px;
    margin-top: 4px;
    grid-column: 1 / 2;
    grid-row: 3;
    --qty-btn-size: 34px;
    --qty-font: 14px;
    --qty-value-font: 15px;
}

.pos-quantity-btn {
    width: var(--qty-btn-size);
    height: var(--qty-btn-size);
    border: none;
    border-radius: var(--pos-border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--pos-transition);
    font-weight: 600;
    font-size: var(--qty-font);
    position: relative;
    overflow: hidden;
}

.pos-quantity-value {
    font-size: var(--qty-value-font);
    font-weight: 700;
    color: var(--pos-gray-600);
    min-width: 46px;
    text-align: center;
    background: var(--pos-gray-50);
    border-radius: var(--pos-border-radius);
    padding: 6px 6px;
    border: 1px solid var(--pos-gray-200);
    transition: var(--pos-transition);
}

/* TOTAL DEL ITEM - EN LA MISMA FILA QUE LOS CONTROLES */
.pos-quantity-total {
    font-size: 12px;
    font-weight: 700;
    color: var(--pos-success);
    background: rgba(16, 185, 129, 0.1);
    padding: 6px 8px;
    border-radius: var(--pos-border-radius);
    border: 1px solid rgba(16, 185, 129, 0.2);
    display: flex;
    align-items: center;
    white-space: nowrap;
    grid-column: 2 / 3;
    grid-row: 3;
    align-self: center;
    min-height: 36px;
}

/* NUEVOS ESTILOS PARA LAYOUT INLINE CON CONTROLES */
.pos-cart-item-name-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    gap: 12px;
    flex: 1;
    padding: 4px 6px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    border: 1px solid var(--pos-gray-200);
}

.pos-cart-product-name {
    flex: 1;
    font-size: var(--cart-item-font-size);
    font-weight: 600;
    color: var(--pos-gray-800);
    line-height: 1.3;
    min-width: 0;
    word-wrap: break-word;
}

.pos-inline-controls {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--pos-gray-100);
    padding: 2px;
    border-radius: 6px;
    border: 1px solid var(--pos-gray-300);
    flex-shrink: 0;
}

.pos-btn-minus,
.pos-btn-plus {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 4px;
    background: white;
    color: var(--pos-gray-600);
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--pos-transition-fast);
    box-shadow: var(--pos-shadow-sm);
}

.pos-btn-minus:hover {
    background: var(--pos-danger);
    color: white;
    transform: scale(1.1);
}

.pos-btn-plus:hover {
    background: var(--pos-success);
    color: white;
    transform: scale(1.1);
}

.pos-btn-minus:active,
.pos-btn-plus:active,
.pos-btn-minus.pressed,
.pos-btn-plus.pressed {
    transform: scale(0.9);
}

.pos-btn-minus:disabled,
.pos-btn-plus:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
}

.pos-quantity-display {
    font-size: 12px;
    font-weight: 700;
    color: var(--pos-gray-700);
    min-width: 20px;
    text-align: center;
    background: white;
    padding: 2px 4px;
    border-radius: 3px;
}

.pos-final-price {
    font-size: 12px;
    color: var(--pos-success);
    font-weight: 700;
    white-space: nowrap;
    background: rgba(16, 185, 129, 0.1);
    padding: 4px 6px;
    border-radius: 6px;
    border: 1px solid rgba(16, 185, 129, 0.2);
    min-width: 60px;
    text-align: center;
    flex-shrink: 0;
}

/* TOTALES DEL CARRITO - DISEÑO MÓVIL OPTIMIZADO */
.pos-cart-totals {
    padding: 8px 10px;
    background: white;
    border: 1px solid var(--pos-border-subtle);
    border-radius: var(--pos-border-radius);
    margin: 4px;
    flex-shrink: 0;
    box-shadow: var(--pos-shadow-sm);
}

.pos-totals-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: clamp(8px, 1.5vw, 12px);
    padding-bottom: clamp(6px, 1vw, 8px);
    border-bottom: 1px solid var(--pos-gray-200);
}

.pos-totals-header-title {
    font-size: clamp(12px, 2vw, 14px);
    font-weight: 600;
    color: var(--pos-gray-600);
    display: flex;
    align-items: center;
    gap: 6px;
}

.pos-items-count {
    font-size: clamp(11px, 1.8vw, 13px);
    color: var(--pos-gray-400);
    background: var(--pos-gray-100);
    padding: 2px 8px;
    border-radius: 12px;
}

.pos-totals-container {
    background: var(--pos-gray-50);
    padding: 4px 6px;
    border-radius: var(--pos-border-radius);
    margin-bottom: 2px;
}

.pos-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: clamp(6px, 1vw, 8px);
    font-size: clamp(13px, 2.2vw, 15px);
    line-height: 1.4;
    color: var(--pos-gray-600);
}

.pos-total-row.final {
    padding: 4px 6px;
    border-top: 1px solid var(--pos-primary);
    font-weight: 700;
    font-size: 13px;
    color: var(--pos-success);
    background: rgba(16, 185, 129, 0.05);
    margin: 2px -6px -4px -6px;
    border-radius: 0 0 var(--pos-border-radius) var(--pos-border-radius);
}

/* BOTONES DE ACCIÓN - DISEÑO MÓVIL */
.pos-action-btn {
    width: 100%;
    padding: 12px 8px;
    border: none;
    border-radius: 6px;
    font-size: clamp(12px, 2vw, 14px);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 6px;
    min-height: 44px;
}

.pos-action-btn.success {
    background: var(--pos-success);
    color: white;
}

/* CONTENEDOR DEL PRECIO Y OPCIONES SELECCIONADAS */
.pos-cart-item-price-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}

/* OPCIÓN SELECCIONADA DEBAJO DEL PRECIO - ESTILO COMPACTO */
.pos-selected-option-display {
    background-color: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 4px;
    padding: 4px 6px;
    font-size: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 2px;
}

.pos-selected-option-tag {
    background-color: #3b82f6;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 9px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    line-height: 1.2;
}

/* RESPONSIVE ESPECÍFICO PARA EL CARRITO EN MÓVILES */
@media (max-width: 767px) {
    .pos-main-container {
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr auto;
        grid-template-areas:
            "sidebar"
            "products"
            "cart";
        height: calc(100vh - 80px);
        padding: 4px;
        gap: 4px;
    }

    .pos-cart {
        max-height: none;
        height: auto;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: visible;
        margin-top: 8px;
    }

    .pos-cart-items {
        max-height: 350px;
        min-height: 180px;
    }

    .pos-cart-item {
        padding: 10px 8px;
    }

    .pos-cart-item-name {
        font-size: 13px;
        font-weight: 800;
        min-height: 36px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        padding: 6px 8px;
        margin-bottom: 3px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        letter-spacing: 0.2px;
        color: #0f172a;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
    }

    .pos-cart-item-price {
        font-size: 12px;
    }

    .pos-cart-item-footer {
        margin-top: 6px;
        padding-top: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    
    .pos-quantity-controls {
        padding: 4px 6px;
        gap: 4px;
        --qty-btn-size: 28px;
        --qty-font: 12px;
        --qty-value-font: 13px;
    }

    .pos-quantity-btn {
        width: var(--qty-btn-size);
        height: var(--qty-btn-size);
        font-size: var(--qty-font);
    }

    .pos-quantity-value {
        min-width: 36px;
        font-size: var(--qty-value-font);
        padding: 4px;
    }

    .pos-quantity-total {
        font-size: 11px;
        padding: 3px 6px;
    }

    .pos-item-remove-btn {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }

    .pos-cart-totals {
        padding: 14px 12px;
        margin: 4px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .pos-totals-container {
        padding: 12px;
    }

    .pos-total-row {
        font-size: 14px;
    }

    .pos-total-row.final {
        font-size: 17px;
        margin: 10px -12px -12px -12px;
        padding-left: 12px;
        padding-right: 12px;
    }

    .pos-action-btn {
        padding: 14px 8px;
        font-size: 14px;
        min-height: 48px;
        margin-bottom: 4px;
    }

    /* Optimización para pantallas muy pequeñas */
    @media (max-width: 480px) {
        .pos-cart {
            margin-top: 4px;
        }

        .pos-cart-items {
            max-height: 220px;
        }

        .pos-cart-item-name {
            min-height: 32px;
            font-size: 12px;
            font-weight: 800;
            padding: 5px 6px;
            margin-bottom: 2px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            letter-spacing: 0.2px;
            color: #0f172a;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
        }

        .pos-quantity-controls {
            padding: 8px;
        }

        .pos-quantity-btn {
            width: 38px;
            height: 38px;
            font-size: 16px;
        }

        .pos-quantity-value {
            min-width: 44px;
            font-size: 16px;
            padding: 8px 4px;
        }

        .pos-item-remove-btn {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .pos-cart-totals {
            padding: 12px 10px;
        }

        .pos-total-row {
            font-size: 13px;
        }

        .pos-total-row.final {
            font-size: 15px;
        }

        .pos-action-btn {
            padding: 12px 6px;
            font-size: 13px;
            min-height: 44px;
        }
    }
}

/* Optimización para tablets en modo portrait */
@media (min-width: 481px) and (max-width: 767px) and (orientation: portrait) {
    .pos-cart-items {
        max-height: 400px;
        min-height: 200px;
    }

    .pos-cart-item-name {
        min-height: 38px;
        font-weight: 800;
        padding: 6px 8px;
        margin-bottom: 3px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        letter-spacing: 0.2px;
        color: #0f172a;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
    }
}

/* Optimización para tablets en modo landscape */
@media (min-width: 768px) and (max-width: 1023px) and (orientation: landscape) {
    .pos-main-container {
        grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
        height: calc(100vh - 80px);
    }

    .pos-cart {
        max-height: none;
        height: 100%;
    }

    .pos-cart-items {
        max-height: calc(100vh - 320px);
        min-height: 220px;
    }
}



/* ACCIONES RÁPIDAS - DISEÑO GESTALT APLICADO */
.pos-quick-actions {
    margin-top: 4px;
    /* Principio de Proximidad: padding coherente con otros componentes */
    padding: 8px;
    /* Principio de Similitud: coherencia con header y otros elementos */
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
    border-radius: 8px;
    border: 1px solid var(--pos-border-subtle);
    position: relative;
    z-index: 10;
    /* Principio de Cierre: contenedor bien definido */
    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.04),
        0 1px 3px rgba(0, 0, 0, 0.03),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
    /* Principio de Continuidad: transición suave */
    transition: all 0.2s ease;
}

/* Efecto de profundidad superior - Principio de Figura-Fondo */
.pos-quick-actions::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg,
        var(--pos-danger) 0%,
        var(--pos-warning) 25%,
        var(--pos-success) 50%,
        var(--pos-primary) 75%,
        var(--pos-secondary) 100%);
    border-radius: 12px 12px 0 0;
    z-index: 1;
}

/* Efecto sutil de textura - Principio de Figura-Fondo */
.pos-quick-actions::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 10% 10%, rgba(239, 68, 68, 0.02) 0%, transparent 40%),
        radial-gradient(circle at 90% 90%, rgba(99, 102, 241, 0.02) 0%, transparent 40%);
    pointer-events: none;
    z-index: 1;
    border-radius: 12px;
}

.pos-quick-actions:hover {
    /* Principio de Figura-Fondo: feedback sutil */
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
    border-color: rgba(99, 102, 241, 0.3);
    transform: translateY(-1px);
    box-shadow:
        0 6px 16px rgba(0, 0, 0, 0.08),
        0 3px 8px rgba(0, 0, 0, 0.06),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.pos-quick-actions-title {
    font-size: 7px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 1px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    opacity: 0.8;
}

/* GRID DE ACCIONES RÁPIDAS - DISEÑO GESTALT */
.pos-quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    /* Principio de Proximidad: espaciado equilibrado */
    gap: clamp(1px, 0.2vw, 3px);
    position: relative;
    z-index: 2;
    /* Principio de Continuidad: transición suave */
    transition: all 0.2s ease;
}

/* Efecto de entrada escalonada para botones - Principio de Continuidad */
.pos-quick-actions-grid .pos-quick-action-btn {
    animation: fadeInAction 0.4s ease-out forwards;
    opacity: 0;
    transform: translateY(10px);
}

.pos-quick-actions-grid .pos-quick-action-btn:nth-child(1) { animation-delay: 0.05s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(2) { animation-delay: 0.1s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(3) { animation-delay: 0.15s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(4) { animation-delay: 0.2s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(5) { animation-delay: 0.25s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(6) { animation-delay: 0.3s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(7) { animation-delay: 0.35s; }
.pos-quick-actions-grid .pos-quick-action-btn:nth-child(8) { animation-delay: 0.4s; }

@keyframes fadeInAction {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* BOTONES DE ACCIONES RÁPIDAS - DISEÑO GESTALT */
.pos-quick-action-btn {
    /* Principio de Proximidad: padding equilibrado */
    padding: 4px 3px !important;
    /* Principio de Similitud: coherencia con otros botones del sistema */
    border-radius: 6px !important;
    font-size: clamp(6px, 1vw, 8px) !important;
    font-weight: 600 !important;
    text-align: center !important;
    cursor: pointer !important;
    /* Principio de Continuidad: transiciones suaves */
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: relative !important;
    min-height: clamp(24px, 3.5vw, 28px) !important;
    height: clamp(24px, 3.5vw, 28px) !important;
    /* Principio de Cierre: sombras que definen límites */
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.06) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
    width: 100% !important;
    overflow: hidden !important;
    touch-action: manipulation !important;
    user-select: none !important;
    white-space: nowrap !important;
    text-overflow: ellipsis !important;
    color: white !important;
    /* Principio de Figura-Fondo: bordes sutiles */
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    /* Efectos de texto mejorados */
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.25) !important;
    letter-spacing: 0.2px !important;
}

/* ESTADOS INTERACTIVOS - PRINCIPIOS GESTALT APLICADOS */
.pos-quick-action-btn:hover:not(:disabled) {
    /* Principio de Figura-Fondo: elevación sutil */
    transform: translateY(-2px) scale(1.02) !important;
    box-shadow:
        0 4px 8px rgba(0, 0, 0, 0.15) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
    /* Principio de Continuidad: brillo sutil */
    filter: brightness(1.1) !important;
}

.pos-quick-action-btn:active:not(:disabled) {
    transform: translateY(0) scale(0.98) !important;
    box-shadow:
        0 1px 2px rgba(0, 0, 0, 0.1) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
    transition: transform 0.1s ease !important;
    filter: brightness(0.95) !important;
}

/* Efecto de pulso para botones activos - Principio de Continuidad */
.pos-quick-action-btn:not(:disabled)::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.pos-quick-action-btn:hover:not(:disabled)::before {
    transform: translateX(100%);
}

/* JERARQUÍA VISUAL - TAMAÑOS MÍNIMOS */
.pos-quick-action-btn.primary {
    border-width: 1px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.pos-quick-action-btn.primary .pos-quick-action-icon {
    width: 12px;
    height: 12px;
}

.pos-quick-action-btn.secondary {
    font-weight: 500;
}

.pos-quick-action-btn.secondary .pos-quick-action-icon {
    width: 11px;
    height: 11px;
}

.pos-quick-action-btn.tertiary {
    opacity: 0.9;
    font-size: 7px;
}

.pos-quick-action-btn.tertiary .pos-quick-action-icon {
    width: 10px;
    height: 10px;
}

/* COLORES GESTALT - PALETA POS PROFESIONAL MEJORADA */
.pos-quick-action-btn.btn-mapa {
    /* Principio de Similitud: gradiente coherente pero distintivo */
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 50%, #60a5fa 100%) !important;
    border: 1px solid rgba(29, 78, 216, 0.8) !important;
    color: #ffffff !important;
    /* Principio de Cierre: sombra que define el botón */
    box-shadow:
        0 2px 6px rgba(29, 78, 216, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-mapa:hover:not(:disabled) {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%) !important;
    box-shadow:
        0 4px 12px rgba(29, 78, 216, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-comanda {
    background: linear-gradient(135deg, #056f57 0%, #0d9488 50%, #14b8a6 100%) !important;
    border: 1px solid rgba(5, 111, 87, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(5, 111, 87, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-comanda:hover:not(:disabled) {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%) !important;
    box-shadow:
        0 4px 12px rgba(5, 111, 87, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-precuenta {
    background: linear-gradient(135deg, #b45309 0%, #ea580c 50%, #f97316 100%) !important;
    border: 1px solid rgba(180, 83, 9, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(180, 83, 9, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-precuenta:hover:not(:disabled) {
    background: linear-gradient(135deg, #92400e 0%, #c2410c 50%, #ea580c 100%) !important;
    box-shadow:
        0 4px 12px rgba(180, 83, 9, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-reabrir {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #8b5cf6 100%) !important;
    border: 1px solid rgba(79, 70, 229, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(79, 70, 229, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-reabrir:hover:not(:disabled) {
    background: linear-gradient(135deg, #4338ca 0%, #6d28d9 50%, #7c3aed 100%) !important;
    box-shadow:
        0 4px 12px rgba(79, 70, 229, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-dividir {
    background: linear-gradient(135deg, #0891b2 0%, #0d9488 50%, #06b6d4 100%) !important;
    border: 1px solid rgba(8, 145, 178, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(8, 145, 178, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-dividir:hover:not(:disabled) {
    background: linear-gradient(135deg, #0e7490 0%, #0f766e 50%, #0891b2 100%) !important;
    box-shadow:
        0 4px 12px rgba(8, 145, 178, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-transferir {
    background: linear-gradient(135deg, #3730a3 0%, #5b21b6 50%, #7c2d92 100%) !important;
    border: 1px solid rgba(55, 48, 163, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(55, 48, 163, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-transferir:hover:not(:disabled) {
    background: linear-gradient(135deg, #312e81 0%, #4c1d95 50%, #581c87 100%) !important;
    box-shadow:
        0 4px 12px rgba(55, 48, 163, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-liberar {
    background: linear-gradient(135deg, #444054 0%, #575366 50%, #6b7280 100%) !important;
    border: 1px solid rgba(68, 64, 84, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(68, 64, 84, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-liberar:hover:not(:disabled) {
    background: linear-gradient(135deg, #374151 0%, #4b5563 50%, #6b7280 100%) !important;
    box-shadow:
        0 4px 12px rgba(68, 64, 84, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

.pos-quick-action-btn.btn-cancelar {
    background: linear-gradient(135deg, #991b1b 0%, #ef4444 50%, #f87171 100%) !important;
    border: 1px solid rgba(153, 27, 27, 0.8) !important;
    color: #ffffff !important;
    box-shadow:
        0 2px 6px rgba(153, 27, 27, 0.25) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
}

.pos-quick-action-btn.btn-cancelar:hover:not(:disabled) {
    background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 50%, #ef4444 100%) !important;
    box-shadow:
        0 4px 12px rgba(153, 27, 27, 0.35) !important,
        inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
}

/* ESTADO DISABLED - MÁS SUTIL */
.pos-quick-action-btn:disabled {
    background: var(--pos-gray-50) !important;
    border-color: var(--pos-gray-200) !important;
    color: var(--pos-gray-400) !important;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.03) !important;
    opacity: 0.7;
}

.pos-quick-action-btn:disabled .pos-quick-action-icon {
    opacity: 0.5;
}

/* ICONOS - TAMAÑO MÍNIMO */
.pos-quick-action-icon {
    width: 9px;
    height: 9px;
    flex-shrink: 0;
    transition: var(--pos-transition-fast);
}

/* ETIQUETAS DE TEXTO - LEGIBLES EN ESPACIO MÍNIMO */
.pos-quick-action-btn .btn-label {
    font-size: 7px;
    font-weight: 700;
    line-height: 1.0;
    margin-top: 0;
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
    max-width: 100%;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    min-height: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 1px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.pos-quick-action-btn.primary .btn-label {
    font-size: 7.5px;
    font-weight: 800;
}

.pos-quick-action-btn.secondary .btn-label {
    font-size: 7px;
    font-weight: 700;
}

.pos-quick-action-btn.tertiary .btn-label {
    font-size: 6.5px;
    font-weight: 700;
}

/* RESPONSIVE PARA MÓVILES */
@media (max-width: 767px) {
    .pos-quick-actions {
        padding: 4px;
        margin-top: 3px;
    }

    .pos-quick-actions-grid {
        grid-template-columns: repeat(6, 1fr);
        gap: 1px;
    }

    .pos-quick-action-btn {
        min-height: 20px;
        height: 20px;
        padding: 2px 1px;
        font-size: 6px;
    }

    .pos-quick-action-icon {
        width: 8px;
        height: 8px;
    }

    .pos-quick-action-btn .btn-label {
        min-height: 8px;
        font-size: 6px;
        font-weight: 700;
    }
}

    @media (max-width: 480px) {
        .pos-quick-actions {
            padding: 3px;
            margin-top: 2px;
        }

        .pos-quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1px;
        }

        .pos-quick-action-btn {
            min-height: 18px;
            height: 18px;
            padding: 1px;
            font-size: 5px;
        }

        .pos-quick-action-icon {
            width: 7px;
            height: 7px;
        }

        .pos-quick-action-btn .btn-label {
            min-height: 7px;
            font-size: 5px;
            font-weight: 700;
            display: none; /* En pantallas muy pequeñas, ocultar texto */
        }
    }
}

/* RESPONSIVE PARA TABLETS */
@media (min-width: 768px) and (max-width: 1023px) {
    .pos-quick-actions {
        padding: 6px;
        margin-top: 3px;
    }

    .pos-quick-action-btn {
        min-height: 24px;
        height: 24px;
        padding: 3px 2px;
        font-size: 7px;
    }

    .pos-quick-action-icon {
        width: 8px;
        height: 8px;
    }

    .pos-quick-action-btn .btn-label {
        min-height: 9px;
        font-size: 6px;
        font-weight: 700;
    }
}



/* MODAL DE PRE-CUENTA - RESPONSIVO Y ADAPTATIVO */
.fi-modal[data-modal-id*="printComanda"] .fi-modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--pos-gray-200);
    width: 100%; /* Eliminar ancho fijo */
    max-width: 80vw; /* Máximo 80% del viewport */
    min-width: 300px; /* Mínimo para móviles */
    animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 0; /* Quitar padding si ya está en el header/body */
}

/* CUERPO DEL MODAL - ADAPTATIVO */
.fi-modal[data-modal-id*="printComanda"] .fi-modal-body {
    padding: 16px;
    background: white;
    max-height: 60vh; /* Evitar modals muy altos */
    overflow-y: auto; /* Scroll vertical si es necesario */
}

/* LISTA DE PRODUCTOS - RESPONSIVO */
.fi-modal[data-modal-id*="printComanda"] .pre-cuenta-products {
    width: 100%;
    margin: 0 -16px; /* Compensar padding */
}

.fi-modal[data-modal-id*="printComanda"] .pre-cuenta-product {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--pos-gray-200);
}

/* TOTALES DEL MODAL - AJUSTADOS */
.fi-modal[data-modal-id*="printComanda"] .pre-cuenta-totals {
    margin-top: 20px;
    text-align: right;
    font-weight: 600;
}

/* RESPONSIVE PARA DISPOSITIVOS PEQUEÑOS */
@media (max-width: 767px) {
    .fi-modal[data-modal-id*="printComanda"] .fi-modal-content {
        max-width: 95vw;
        min-width: 320px;
    }

    .fi-modal[data-modal-id*="printComanda"] .pre-cuenta-product {
        font-size: 13px;
    }

    .fi-modal[data-modal-id*="printComanda"] .pre-cuenta-totals {
        font-size: 14px;
    }
}






























        /* TOTALES DEL CARRITO MEJORADOS - UX OPTIMIZADO */
        .pos-cart-totals {
            padding: 6px 8px;
            background: white;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 4px;
            flex-shrink: 0;
            box-shadow: var(--pos-shadow-sm);
        }

        .pos-totals-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid var(--pos-gray-200);
        }

        .pos-totals-header-title {
            font-size: clamp(12px, 2vw, 14px);
            font-weight: 600;
            color: var(--pos-gray-600);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pos-items-count {
            font-size: clamp(11px, 1.8vw, 13px);
            color: var(--pos-gray-400);
            background: var(--pos-gray-100);
            padding: 2px 8px;
            border-radius: 12px;
        }

        .pos-totals-container {
            background: var(--pos-gray-50);
            padding: 6px 8px;
            border-radius: var(--pos-border-radius);
            margin-bottom: 4px;
        }

        .pos-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
            font-size: 13px;
            line-height: 1.2;
            color: var(--pos-gray-600);
        }

        .pos-total-row:last-child {
            margin-bottom: 0;
        }

        .pos-total-row.final {
            padding-top: 4px;
            border-top: 2px solid var(--pos-primary);
            font-weight: 700;
            font-size: 15px;
            color: var(--pos-success);
            background: rgba(16, 185, 129, 0.05);
            margin: 4px -8px -6px -8px;
            padding-left: 8px;
            padding-right: 8px;
            border-radius: 0 0 var(--pos-border-radius) var(--pos-border-radius);
        }

        .pos-total-amount {
            font-weight: 600;
        }

        .pos-total-row.final .pos-total-amount {
            font-weight: 800;
            font-size: 16px;
        }

        /* INDICADORES DE ESTADO MEJORADOS */
        .pos-totals-header-title svg {
            flex-shrink: 0;
        }

        .pos-totals-header-title span {
            font-weight: 600;
        }

        /* RESPONSIVE PARA MÓVILES */
        @media (max-width: 767px) {
            .pos-cart-totals {
                padding: 4px 6px;
            }

            .pos-totals-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                margin-bottom: 3px;
                padding-bottom: 2px;
            }

            .pos-items-count {
                align-self: flex-end;
            }

            .pos-total-row.final {
                margin: 3px -6px -4px -6px;
                padding-left: 6px;
                padding-right: 6px;
                font-size: 14px;
            }

            .pos-total-row.final .pos-total-amount {
                font-size: 15px;
            }
        }

        /* ANIMACIÓN SUTIL PARA CAMBIOS DE ESTADO */
        .pos-totals-header-title {
            transition: var(--pos-transition);
        }

        .pos-total-amount {
            transition: var(--pos-transition);
        }

        /* BOTONES DE ACCIÓN - PATRÓN POS COMPACTO */
        .pos-action-btn {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-bottom: 4px;
        }

        /* BOTÓN SUCCESS ULTRA COMPACTO */
        .pos-action-btn.success {
            padding: 6px;
            font-size: 11px;
        }

        .pos-action-btn.primary {
            background: var(--pos-primary);
            color: white;
        }

        .pos-action-btn.success {
            background: var(--pos-success);
            color: white;
        }

        .pos-action-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--pos-shadow-hover);
        }

        .pos-action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* LOGO DEL SISTEMA - DISEÑO GESTALT APLICADO */
        .pos-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 400px;
            /* Principio de Proximidad: espaciado coherente */
            padding: clamp(32px, 5vw, 48px) clamp(16px, 3vw, 24px);
            text-align: center;
            /* Principio de Figura-Fondo: fondo sutil que realza el logo */
            background:
                radial-gradient(circle at center, rgba(99, 102, 241, 0.03) 0%, transparent 70%),
                radial-gradient(circle at 30% 70%, rgba(16, 185, 129, 0.02) 0%, transparent 50%);
            border-radius: 12px;
            margin: 20px;
            /* Principio de Cierre: contenedor definido */
            border: 1px solid rgba(226, 232, 240, 0.5);
            position: relative;
            z-index: 2;
            /* Principio de Continuidad: transición suave */
            transition: all 0.4s ease;
        }

        .pos-logo-container:hover {
            /* Principio de Figura-Fondo: feedback sutil */
            background:
                radial-gradient(circle at center, rgba(99, 102, 241, 0.05) 0%, transparent 70%),
                radial-gradient(circle at 30% 70%, rgba(16, 185, 129, 0.03) 0%, transparent 50%);
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }

        .pos-system-logo {
            width: clamp(320px, 45vw, 400px);
            height: auto;
            max-width: 100%;
            /* Principio de Proximidad: espaciado con el texto */
            margin-bottom: clamp(36px, 5vw, 44px);
            opacity: 0.85;
            /* Principio de Continuidad: transición elegante */
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            /* Principio de Cierre: sombra sutil */
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.06));
        }

        .pos-system-logo:hover {
            opacity: 1;
            transform: scale(1.03);
            filter: drop-shadow(0 6px 12px rgba(99, 102, 241, 0.15));
        }

        .pos-logo-text {
            /* Principio de Similitud: tipografía coherente */
            font-size: clamp(14px, 2.2vw, 18px);
            font-weight: 500;
            color: #6b7280;
            margin: 0;
            opacity: 0.8;
            letter-spacing: 0.3px;
            line-height: 1.4;
            /* Principio de Continuidad: transición suave */
            transition: all 0.3s ease;
        }

        .pos-logo-container:hover .pos-logo-text {
            color: #4b5563;
            opacity: 1;
        }

        /* ESTADO VACÍO - DISEÑO GESTALT APLICADO */
        .pos-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* Principio de Proximidad: espaciado coherente */
            padding: clamp(32px, 5vw, 48px) clamp(16px, 3vw, 24px);
            text-align: center;
            /* Principio de Figura-Fondo: color sutil que no compite */
            color: #6b7280;
            /* Principio de Cierre: contenedor definido */
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(241, 245, 249, 0.8) 100%);
            border: 2px dashed var(--pos-border-subtle);
            border-radius: 12px;
            margin: 20px;
            /* Principio de Simetría: balance visual */
            position: relative;
            z-index: 2;
            /* Principio de Continuidad: transición suave */
            transition: all 0.3s ease;
        }

        .pos-empty-state:hover {
            /* Principio de Figura-Fondo: feedback sutil */
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%);
            border-color: var(--pos-primary);
            transform: translateY(-2px);
        }

        .pos-empty-icon {
            width: clamp(40px, 6vw, 56px);
            height: clamp(40px, 6vw, 56px);
            margin-bottom: clamp(12px, 2vw, 20px);
            /* Principio de Figura-Fondo: icono sutil pero visible */
            opacity: 0.6;
            color: var(--pos-gray-400);
            /* Principio de Continuidad: animación suave */
            transition: all 0.3s ease;
        }

        .pos-empty-state:hover .pos-empty-icon {
            opacity: 0.8;
            color: var(--pos-primary);
            transform: scale(1.05);
        }

        /* Títulos y texto del estado vacío - Principio de Similitud */
        .pos-empty-state h3 {
            /* Principio de Similitud: tipografía coherente */
            font-size: clamp(16px, 2.5vw, 20px);
            font-weight: 600;
            color: #374151;
            margin-bottom: clamp(6px, 1vw, 10px);
            letter-spacing: 0.3px;
        }

        .pos-empty-state p {
            font-size: clamp(13px, 2vw, 16px);
            font-weight: 400;
            color: #6b7280;
            line-height: 1.4;
            margin: 0;
        }

        /* BREAKPOINTS RESPONSIVOS MEJORADOS */

        /* Pantallas grandes (1400px+) */
        @media (min-width: 1400px) {
            :root {
                --pos-cart-width: 480px;
                --pos-sidebar-width: 220px;
                --pos-product-min-width: 150px;
                --pos-gap: 8px;
            }
        }

        /* Pantallas de 13 pulgadas (1200px - 1399px) - Optimización específica */
        @media (max-width: 1399px) and (min-width: 1200px) {
            :root {
                --pos-cart-width: 380px;
                --pos-sidebar-width: 180px;
                --pos-product-min-width: 120px;
                --pos-gap: 6px;
            }

            .pos-main-container {
                grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
                height: calc(100vh - 110px);
                min-height: 350px; /* Optimizado para 13 pulgadas */
                padding: 6px;
                gap: 6px;
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .pos-product-card {
                min-height: 80px;
                padding: 6px;
            }

            .pos-products-grid {
                padding: 6px; /* Menos padding para más espacio */
                margin: 3px; /* Menos margen */
            }

            .pos-products-container {
                padding: 4px; /* Optimizado para 13 pulgadas */
                gap: 4px;
            }
        }

        /* Laptops medianos (1024px - 1199px) */
        @media (max-width: 1199px) and (min-width: 1024px) {
            :root {
                --pos-cart-width: 350px;
                --pos-sidebar-width: 160px;
                --pos-product-min-width: 110px;
                --pos-gap: 5px;
            }

            .pos-main-container {
                grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
                height: calc(100vh - 100px);
                min-height: 320px; /* Optimizado para laptops medianos */
                padding: 5px;
                gap: 5px;
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 3px;
            }

            .pos-product-card {
                min-height: 75px;
                padding: 5px;
            }

            .pos-product-name {
                font-size: 11px;
                line-height: 1.2;
            }

            .pos-product-price {
                font-size: 10px;
            }

            .pos-products-grid {
                padding: 5px; /* Menos padding para laptops medianos */
                margin: 2px; /* Menos margen */
            }

            .pos-products-container {
                padding: 3px; /* Optimizado para laptops medianos */
                gap: 3px;
            }
        }

        /* Tablets (768px - 1023px) */
        @media (max-width: 1023px) and (min-width: 768px) {
            :root {
                --pos-cart-width: 370px;
                --pos-sidebar-width: 170px;
                --pos-product-min-width: 120px;
                --pos-gap: 4px;
            }

            .pos-main-container {
                grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
                height: calc(100vh - 100px);
                padding: 4px;
            }

            .pos-category-btn {
                padding: 8px 4px;
                font-size: 13px; /* Ligeramente mayor en tablets */
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 4px;
            }

            .pos-quick-action-btn {
                min-height: 38px;
                height: 38px;
                padding: 8px 4px;
            }

            .pos-quick-action-btn.primary {
                min-height: 42px;
                height: 42px;
            }

            .pos-quick-action-btn.secondary {
                min-height: 40px;
                height: 40px;
            }

            .pos-quick-action-icon {
                width: 14px;
                height: 14px;
            }
        }

        /* Móviles (hasta 767px) */
        @media (max-width: 767px) {
            :root {
                --pos-gap: 4px;
            }

            .pos-main-container {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr auto;
                grid-template-areas:
                    "sidebar"
                    "products"
                    "cart";
                height: calc(100vh - 80px);
                padding: 4px;
            }

            .pos-categories {
                max-height: 120px;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .pos-categories-content {
                display: flex;
                flex-direction: row;
                gap: 4px;
                padding: 4px;
            }

            .pos-category-btn {
                min-width: 80px;
                padding: 8px 12px;
                font-size: 12px; /* Un poco mayor en móviles */
                white-space: nowrap;
            }

            .pos-cart {
                max-height: 200px;
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 3px;
            }

            .pos-quick-action-btn {
                min-height: 32px;
                height: 32px;
                padding: 6px 3px;
                font-size: 8px;
            }

            .pos-quick-action-btn.primary {
                min-height: 36px;
                height: 36px;
            }

            .pos-quick-action-btn.secondary {
                min-height: 34px;
                height: 34px;
            }

            .pos-quick-action-btn .btn-label {
                font-size: 5px;
            }

            .pos-quick-action-icon {
                width: 10px;
                height: 10px;
            }
        }

        /* Pantallas muy pequeñas (hasta 480px) */
        @media (max-width: 480px) {
            .pos-main-container {
                padding: 2px;
                gap: 2px;
            }

            .pos-categories-header h3 {
                font-size: 12px;
            }

            .pos-product-card {
                min-height: 60px;
                padding: 2px;
            }

            .pos-product-name {
                font-size: 11px; /* Un poco mayor en móviles */
                line-height: 1.2;
            }

            .pos-product-price {
                font-size: 10px;
            }

            .pos-cart-header h3 {
                font-size: 12px;
            }
        }

        /* SCROLLBARS PERSONALIZADOS */
        .pos-categories-content::-webkit-scrollbar,
        .pos-cart-items::-webkit-scrollbar,
        .pos-products-grid::-webkit-scrollbar {
            width: 6px;
        }

        .pos-categories-content::-webkit-scrollbar-track,
        .pos-cart-items::-webkit-scrollbar-track,
        .pos-products-grid::-webkit-scrollbar-track {
            background: var(--pos-gray-100);
        }

        .pos-categories-content::-webkit-scrollbar-thumb,
        .pos-cart-items::-webkit-scrollbar-thumb,
        .pos-products-grid::-webkit-scrollbar-thumb {
            background: var(--pos-gray-200);
            border-radius: 3px;
        }

        .pos-categories-content::-webkit-scrollbar-thumb:hover,
        .pos-cart-items::-webkit-scrollbar-thumb:hover,
        .pos-products-grid::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ANIMACIONES Y FEEDBACK VISUAL */
        .pos-loading {
            pointer-events: none;
            opacity: 0.7;
            position: relative;
        }

        .pos-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid var(--pos-primary);
            border-radius: 50%;
            animation: pos-spin 1s linear infinite;
        }

        @keyframes pos-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* TOAST NOTIFICATIONS */
        .pos-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 1px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            padding: 16px;
            box-shadow: var(--pos-shadow-hover);
            z-index: 1000;
            animation: pos-slide-in 0.3s ease;
        }

        @keyframes pos-slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            /* ===== Ajustes responsivos adicionales para maximizar densidad del carrito ===== */
            @media (max-width: 1023px) {
                .pos-cart-items { max-height: 320px; min-height: 160px; }
            }
            @media (max-width: 767px) {
                .pos-cart-items { --cart-item-font-size: 10.5px; --cart-item-font-size-sm: 9.5px; --cart-item-padding:5px; max-height: 300px; min-height: 140px; }
                .pos-cart-item { column-gap:4px; }
                .pos-cart-items .pos-item-remove-btn { width:24px; height:24px; min-height:24px; }
            }
            @media (max-width: 480px) {
                .pos-cart-items { --cart-item-font-size: 10px; --cart-item-font-size-sm: 9px; --cart-item-padding:4px; max-height: 280px; min-height: 120px; }
                .pos-cart-items .pos-item-remove-btn { width:22px; height:22px; }
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        /* ========================================= */
        /* OCULTAR SIDEBAR COMPLETAMENTE EN POS */
        /* ========================================= */
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar que estamos en POS
            if (window.location.pathname.includes('pos-interface')) {
                console.log('🎯 POS: Ocultando sidebar completamente');

                // BUSCAR TOGGLE EN MÚLTIPLES UBICACIONES
                let toggle = null;
                const toggleSelectors = [
                    '.fi-topbar [data-sidebar-toggle]',
                    '.fi-header [data-sidebar-toggle]',
                    '.fi-sidebar [data-sidebar-toggle]',
                    '.fi-topbar .fi-sidebar-toggle',
                    '.fi-header .fi-sidebar-toggle',
                    '.fi-sidebar .fi-sidebar-toggle',
                    '.fi-topbar button[aria-label*="sidebar"]',
                    '.fi-header button[aria-label*="sidebar"]',
                    '.fi-sidebar button[aria-label*="sidebar"]',
                    '.fi-topbar button[aria-label*="menu"]',
                    '.fi-header button[aria-label*="menu"]',
                    '.fi-sidebar button[aria-label*="menu"]',
                    '.fi-topbar .fi-icon-btn:first-child',
                    '.fi-header .fi-icon-btn:first-child'
                ];

                for (const selector of toggleSelectors) {
                    toggle = document.querySelector(selector);
                    if (toggle) {
                        console.log('🎯 Toggle encontrado con selector:', selector);
                        break;
                    }
                }

                // Si NO encontramos toggle, crear uno custom
                if (!toggle) {
                    console.log('🎯 Creando toggle custom');
                    toggle = document.createElement('button');
                    toggle.innerHTML = '☰';
                    toggle.setAttribute('aria-label', 'Toggle sidebar');
                }

                // SIEMPRE agregar el atributo para identificarlo después
                toggle.setAttribute('data-sidebar-toggle', 'pos-custom');
                toggle.setAttribute('id', 'pos-sidebar-toggle');

                // POSICIONAR toggle fijo y MUY VISIBLE
                toggle.style.display = 'flex !important';
                toggle.style.position = 'fixed !important';
                toggle.style.top = '20px !important';
                toggle.style.left = '20px !important';
                toggle.style.zIndex = '99999 !important';
                toggle.style.background = '#3b82f6 !important'; // Azul vibrante
                toggle.style.color = 'white !important';
                toggle.style.border = '2px solid #1d4ed8 !important';
                toggle.style.borderRadius = '8px !important';
                toggle.style.padding = '12px !important';
                toggle.style.boxShadow = '0 4px 12px rgba(59, 130, 246, 0.4) !important';
                toggle.style.cursor = 'pointer !important';
                toggle.style.fontSize = '18px !important';
                toggle.style.fontWeight = 'bold !important';
                toggle.style.lineHeight = '1 !important';
                toggle.style.width = '50px !important';
                toggle.style.height = '50px !important';
                toggle.style.alignItems = 'center !important';
                toggle.style.justifyContent = 'center !important';
                toggle.style.transition = 'all 0.2s ease !important';

                // Efecto hover
                toggle.addEventListener('mouseenter', function() {
                    this.style.background = '#1d4ed8 !important';
                    this.style.transform = 'scale(1.1) !important';
                });
                toggle.addEventListener('mouseleave', function() {
                    this.style.background = '#3b82f6 !important';
                    this.style.transform = 'scale(1) !important';
                });

                // Agregar al body para que siempre esté visible
                document.body.appendChild(toggle);
                console.log('🎯 Toggle agregado al body con ID:', toggle.id);

                // OCULTAR sidebar completamente
                const sidebar = document.querySelector('.fi-sidebar');
                if (sidebar) {
                    sidebar.style.display = 'none';
                }

                // EXPANDIR main content al 100%
                const main = document.querySelector('.fi-main');
                if (main) {
                    main.style.marginLeft = '0';
                    main.style.width = '100%';
                    main.style.maxWidth = 'none';
                }

                // EXPANDIR layout principal
                const layout = document.querySelector('.fi-layout');
                if (layout) {
                    layout.style.gridTemplateColumns = '1fr';
                }

                // Función para manejar el toggle del sidebar
                function setupSidebarToggle() {
                    // El toggle ya está garantizado en el body con ID específico
                    const toggleBtn = document.getElementById('pos-sidebar-toggle');
                    if (toggleBtn) {
                        console.log('🎯 Configurando toggle funcional');

                        // Limpiar listeners anteriores
                        const newToggleBtn = toggleBtn.cloneNode(true);
                        toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);

                        newToggleBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const sidebar = document.querySelector('.fi-sidebar');
                            if (sidebar) {
                                if (sidebar.style.display === 'none') {
                                    console.log('🎯 Mostrando sidebar');
                                    // Mostrar sidebar como overlay
                                    sidebar.style.display = 'block';
                                    sidebar.style.position = 'fixed';
                                    sidebar.style.top = '0';
                                    sidebar.style.left = '0';
                                    sidebar.style.height = '100vh';
                                    sidebar.style.zIndex = '9998'; // Menor que el toggle
                                    sidebar.style.background = 'white';
                                    sidebar.style.boxShadow = '2px 0 10px rgba(0,0,0,0.1)';
                                    sidebar.style.width = '16rem'; // Ancho fijo
                                } else {
                                    console.log('🎯 Ocultando sidebar');
                                    // Ocultar sidebar
                                    sidebar.style.display = 'none';
                                }
                            }
                        });
                    } else {
                        console.log('❌ No se encontró toggle en el body');
                    }
                }

                // Configurar toggle después de que todo esté listo
                setTimeout(setupSidebarToggle, 100);
            }
        });

        /* ========================================= */
        /* TOGGLE DE CATEGORÍAS */
        /* ========================================= */
        function toggleCategories() {
            const categoriesSection = document.getElementById('pos-categories');
            const mainContainer = document.querySelector('.pos-main-container');

            if (categoriesSection && mainContainer) {
                // Toggle clase en categorías para animación de deslizamiento
                categoriesSection.classList.toggle('collapsed');

                // Toggle clase en main container para expansión del grid
                mainContainer.classList.toggle('categories-collapsed');
            }
        }

        // INICIALIZAR CATEGORÍAS VISIBLES AL CARGAR (COMENTADO PARA MOSTRAR POR DEFECTO)
        document.addEventListener('DOMContentLoaded', function() {
            const categoriesSection = document.getElementById('pos-categories');
            const mainContainer = document.querySelector('.pos-main-container');

            if (categoriesSection && mainContainer) {
                // Comentado para mostrar categorías por defecto
                // categoriesSection.classList.add('collapsed');
                // mainContainer.classList.add('categories-collapsed');
            }
        });
    </script>

    <script>
    // Solicitar PIN para rol waiter cuando se intenta eliminar o limpiar
    window.addEventListener('pos-pin-required', (event) => {
        try {
            const detail = event?.detail ?? {};
            const action = detail.action ?? null;
            const index = detail.index ?? null;
            const pin = prompt('Ingrese PIN de autorización:');
            if (pin === null || pin === '') return; // cancelado
            const root = document.querySelector('[wire\\:id]');
            const id = root ? root.getAttribute('wire:id') : null;
            if (id && window.Livewire) {
                const component = window.Livewire.find(id);
                if (component) {
                    component.call('verifyPinAndExecute', action, index, pin);
                    return;
                }
            }
            if (window.$wire && typeof window.$wire.verifyPinAndExecute === 'function') {
                window.$wire.verifyPinAndExecute(action, index, pin);
            }
        } catch (e) {
            console.error('Error solicitando PIN:', e);
        }
    });
    </script>
    <div class="pos-interface">
        {{-- BOTÓN TOGGLE FIJO PARA CATEGORÍAS --}}
        <button
            id="categories-toggle-btn"
            onclick="toggleCategories()"
            class="pos-categories-toggle-btn-fixed"
            title="Mostrar/Ocultar Categorías"
        >
            <x-heroicon-o-squares-2x2 class="pos-categories-toggle-icon" />
        </button>

        <div class="pos-main-container">
            {{-- SIDEBAR IZQUIERDO: CATEGORÍAS --}}
            <div class="pos-categories" id="pos-categories">
                <div class="pos-categories-header">
                    <h3 class="text-base font-bold text-gray-800 text-center">CATEGORÍAS</h3>
                </div>

                <div class="pos-categories-content">
                    @foreach($this->getCategoriesProperty() as $category)
                        @php
                            $categorySlug = strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', '–', '"', "'"], ['', 'a', 'e', 'i', 'o', 'u', 'n', 'u', '-', '', ''], $category->name));

                            // Sistema inteligente de colores: categorías específicas + auto-generadas
                            $categoryClass = match($categorySlug) {
                                'sopas' => 'cat-sopas',
                                'ensaladas' => 'cat-ensaladas',
                                'piqueos' => 'cat-piqueos',
                                'pastasartesanales', 'pastas', 'pasta' => 'cat-pastas',
                                'entradas' => 'cat-entradas',
                                'parrillas' => 'cat-parrillas',
                                'pollosalalea', 'pollos', 'pollosalaleña' => 'cat-pollos',
                                'platosdefondofusionqrico', 'platos', 'fondofusion', 'platosdefondo' => 'cat-platos',
                                'bebidas' => 'cat-bebidas',
                                'adicionales' => 'cat-adicionales',
                                'cartaanterior', 'carta', 'menuanterior' => 'cat-carta',
                                default => 'cat-auto-' . $this->generateColorIndex($category->name)
                            };

                            // Generar colores únicos para categorías nuevas
                            $autoColorIndex = $this->generateColorIndex($category->name);
                            $autoColors = $this->getAutoColors($autoColorIndex);
                        @endphp
                        <button
                            wire:click="selectCategory({{ $category->id }})"
                            class="pos-category-btn {{ $selectedCategoryId === $category->id ? 'active' : '' }} {{ $categoryClass }}"
                            @if(str_starts_with($categoryClass, 'cat-auto-'))
                                style="
                                    --auto-bg: {{ $autoColors['bg'] }};
                                    --auto-bg-hover: {{ $autoColors['bgHover'] }};
                                    --auto-bg-active: {{ $autoColors['bgActive'] }};
                                    --auto-border: {{ $autoColors['border'] }};
                                    --auto-border-hover: {{ $autoColors['borderHover'] }};
                                    --auto-border-active: {{ $autoColors['borderActive'] }};
                                    --auto-text: {{ $autoColors['text'] }};
                                    --auto-text-active: {{ $autoColors['textActive'] }};
                                "
                            @endif
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                {{-- SUBCATEGORÍAS CON DISEÑO JERÁRQUICO --}}
                @if($selectedCategoryId && $subcategories->isNotEmpty())
                    <div class="pos-subcategories-container">
                        <h4 class="pos-subcategories-title">SUBCATEGORÍAS</h4>
                        <div class="space-y-1">
                            <button
                                wire:click="selectSubcategory(null)"
                                class="pos-subcategory-btn {{ $selectedSubcategoryId === null ? 'active' : '' }}"
                            >
                                Todos
                            </button>
                            @foreach($subcategories as $subcat)
                                @php
                                    // Determinar la clase CSS para subcategorías de bebidas
                                    $subcategoryClass = '';
                                    if ($selectedCategoryId) {
                                        $parentCategory = \App\Models\ProductCategory::find($selectedCategoryId);
                                        if ($parentCategory && strtolower($parentCategory->name) === 'bebidas') {
                                            $subcatName = strtolower($subcat->name);

                                            // Mapeo específico de subcategorías existentes
                                            if ($subcatName === 'gaseosas') {
                                                $subcategoryClass = 'subcat-gaseosas';
                                            } elseif (str_contains($subcatName, 'bebidas naturales') || $subcatName === 'naturales clásicas') {
                                                $subcategoryClass = 'subcat-naturales';
                                            } elseif ($subcatName === 'bebidas frozen') {
                                                $subcategoryClass = 'subcat-frozen';
                                            } elseif ($subcatName === 'cervezas') {
                                                $subcategoryClass = 'subcat-alcoholicas';
                                            } elseif ($subcatName === 'vinos') {
                                                $subcategoryClass = 'subcat-vinos';
                                            } elseif ($subcatName === 'sangrías') {
                                                $subcategoryClass = 'subcat-sangrias';
                                            } elseif ($subcatName === 'tragos') {
                                                $subcategoryClass = 'subcat-tragos';
                                            }

                                            // Fallback para subcategorías no mapeadas específicamente
                                            elseif (str_contains($subcatName, 'gaseosa') || str_contains($subcatName, 'refresco') || str_contains($subcatName, 'soda')) {
                                                $subcategoryClass = 'subcat-gaseosas';
                                            } elseif (str_contains($subcatName, 'natural') || str_contains($subcatName, 'jugo') || str_contains($subcatName, 'agua')) {
                                                $subcategoryClass = 'subcat-naturales';
                                            } elseif (str_contains($subcatName, 'cerveza') || str_contains($subcatName, 'vino') || str_contains($subcatName, 'licor') || str_contains($subcatName, 'alcohol') || str_contains($subcatName, 'trago')) {
                                                $subcategoryClass = 'subcat-alcoholicas';
                                            } elseif (str_contains($subcatName, 'café') || str_contains($subcatName, 'té') || str_contains($subcatName, 'chocolate') || str_contains($subcatName, 'caliente') || str_contains($subcatName, 'frozen')) {
                                                $subcategoryClass = 'subcat-calientes';
                                            } else {
                                                // Color por defecto para subcategorías no clasificadas
                                                $subcategoryClass = 'subcat-gaseosas';
                                            }
                                        }
                                    }
                                @endphp
                                <button
                                    wire:click="selectSubcategory({{ $subcat->id }})"
                                    class="pos-subcategory-btn {{ $selectedSubcategoryId === $subcat->id ? 'active' : '' }} {{ $subcategoryClass }}"
                                >
                                    {{ $subcat->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ÁREA CENTRAL: PRODUCTOS --}}
            <div class="pos-products-area">
                {{-- BARRA DE BÚSQUEDA --}}
                <div class="pos-search-bar">
                    <div class="pos-search-container" x-data="{ hasText: false }">
                        {{-- Icono de búsqueda --}}
                        <svg class="pos-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>

                        {{-- Input de búsqueda --}}
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar productos..."
                            class="pos-search-input"
                            x-init="$watch('$el.value', value => hasText = value.length > 0)"
                            @input="hasText = $el.value.length > 0"
                        />

                        {{-- Botón limpiar búsqueda --}}
                        <button
                            type="button"
                            class="pos-search-clear"
                            x-show="hasText"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            wire:click="clearSearch"
                            title="Limpiar búsqueda"
                        >
                            ×
                        </button>

                        {{-- Indicador de carga --}}
                        <div class="pos-search-loading" wire:loading wire:target="search">
                            <div class="pos-search-spinner"></div>
                        </div>
                    </div>
                </div>

                {{-- GRID DE PRODUCTOS --}}
                <div class="pos-products-grid">
                    @if(!$selectedCategoryId && !$search)
                        {{-- LOGO DEL SISTEMA - ESTADO INICIAL --}}
                        <div class="pos-logo-container">
                            <img
                                src="{{ asset('images/logoWayna.svg') }}"
                                alt="Logo Wayna"
                                class="pos-system-logo"
                            />
                            <p class="pos-logo-text">Selecciona una categoría para ver los productos</p>
                        </div>
                    @else
                        <div class="pos-products-container">
                            @forelse ($products as $product)
                                <div
                                    wire:click="addToCart({{ $product->id }})"
                                    class="pos-product-card {{ !$canAddProducts ? 'pos-loading' : '' }}"
                                    @if(!$canAddProducts) style="pointer-events: none;" @endif
                                >
                                    {{-- Sección 1: Imagen (Proporción áurea superior - 61.8%) --}}
                                    <div class="pos-product-image">
                                        @if($product->image_path)
                                            <img
                                                src="{{ $product->image }}"
                                                alt="{{ $product->name }}"
                                            />
                                        @else
                                            <span class="product-placeholder">
                                                {{ strtoupper(substr($product->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Sección 2: Contenido integrado (Proporción áurea media - 38.2%) --}}
                                    <div class="pos-product-content">
                                        <div class="pos-product-name">{{ $product->name }}</div>
                                        @if($product->category)
                                            <div class="pos-product-category">{{ $product->category->name }}</div>
                                        @endif
                                    </div>

                                    {{-- Sección 3: Precio (Proporción áurea inferior - 23.6%) --}}
                                    <div class="pos-product-price">S/ {{ number_format($product->sale_price, 2) }}</div>
                                </div>
                            @empty
                                <div class="pos-empty-state" style="grid-column: 1 / -1;">
                                    <x-heroicon-o-shopping-bag class="pos-empty-icon" />
                                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No hay productos</h3>
                                    <p style="font-size: 14px;">
                                        @if($search || $selectedCategoryId)
                                            No se encontraron productos con los filtros aplicados.
                                        @else
                                            No hay productos registrados en el sistema.
                                        @endif
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    @endif

                    {{-- INFORMACIÓN ADICIONAL --}}
                    @if(($selectedCategoryId || $search) && $products && $products->count() > 0)
                        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--pos-gray-200); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; color: #6b7280;">
                                {{ $products->count() }} productos {{ $search || $selectedCategoryId ? 'filtrados' : 'disponibles' }}
                            </span>
                            <span style="font-size: 12px; color: #9ca3af;">
                                Actualizado: {{ now()->format('H:i:s') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- CARRITO DERECHO --}}
            <div class="pos-cart">
                {{-- HEADER DEL CARRITO --}}
                <div class="pos-cart-header">
                    {{-- HEADER REDISEÑADO MEJORADO --}}
                    <div class="pos-cart-title-new">

                        {{-- FILA 1: INFORMACIÓN PRINCIPAL DE MESA/VENTA --}}
                        <div class="pos-cart-main-info">
                            @if($selectedTable)
                                <div class="pos-cart-mesa-badge mesa">
                                    <x-heroicon-s-home class="pos-cart-icon" />
                                    <span class="pos-cart-mesa-label">MESA {{ $selectedTable->number }}</span>
                                </div>
                            @else
                                <div class="pos-cart-mesa-badge venta-directa">
                                    <x-heroicon-s-shopping-cart class="pos-cart-icon" />
                                    <span class="pos-cart-mesa-label">VENTA DIRECTA</span>
                                </div>
                            @endif

                            {{-- BOTÓN LIMPIAR INTEGRADO --}}
                            <button
                                wire:click="clearCart"
                                class="pos-cart-clear-btn-new"
                                title="Limpiar carrito"
                                {{ !$canClearCart ? 'disabled' : '' }}
                            >
                                <x-heroicon-s-trash class="pos-clear-icon" />
                            </button>
                        </div>

                        {{-- FILA 2: MÉTRICAS Y CONTROLES --}}
                        <div class="pos-cart-metrics">
                            {{-- CONTADOR DE PRODUCTOS --}}
                            <div class="pos-cart-product-counter">
                                <x-heroicon-s-shopping-bag class="pos-metric-icon" />
                                <span class="pos-metric-value">{{ count($cartItems) }}</span>
                                <span class="pos-metric-label">productos</span>
                            </div>

                            {{-- SEPARADOR VISUAL --}}
                            <div class="pos-cart-separator"></div>

                            {{-- COMENSALES --}}
                            <div class="pos-cart-guests-control">
                                <x-heroicon-s-users class="pos-metric-icon" />
                                <input
                                    type="number"
                                    wire:model.live="numberOfGuests"
                                    min="1"
                                    max="20"
                                    placeholder="1"
                                    class="pos-guests-input"
                                    required
                                >
                                <span class="pos-metric-label">comensales</span>
                            </div>
                        </div>
                    </div>

                    {{-- ACCIONES RÁPIDAS REORGANIZADAS --}}
                    <div class="pos-quick-actions">
    <div class="pos-quick-actions-grid">
        <!-- Mapa -->
        <button wire:click="mountAction('backToTableMap')" class="pos-quick-action-btn btn-mapa primary" title="Ir al mapa de mesas">
            <span class="btn-label">Mapa</span>
        </button>

        <!-- Comanda -->
        <button wire:click="mountAction('printComanda')" class="pos-quick-action-btn btn-comanda primary" {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }} title="Imprimir comanda para cocina">
            <span class="btn-label">Comanda</span>
        </button>

        <!-- Pre-Cuenta -->
        <button wire:click="mountAction('printPreBillNew')" class="pos-quick-action-btn btn-precuenta secondary" {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }} title="Imprimir pre-cuenta para cliente">
            <span class="btn-label">Pre-Cuenta</span>
        </button>

        <!-- Reabrir -->
        <button wire:click="mountAction('reopen_order_for_editing')" class="pos-quick-action-btn btn-reabrir secondary" {{ !($this->order instanceof \App\Models\Order && !$this->order->invoices()->exists() && $this->order->status !== \App\Models\Order::STATUS_COMPLETED) ? 'disabled' : '' }} title="Reabrir orden para edición">
            <span class="btn-label">Reabrir</span>
        </button>

        <!-- Dividir/Unir -->
        @if($this->tieneCuentasDivididas())
            <button wire:click="unirCuentas" class="pos-quick-action-btn btn-unir success" title="Unir todas las cuentas divididas">
                <span class="btn-label">Unir</span>
            </button>
            <button wire:click="mountAction('split_items')" class="pos-quick-action-btn btn-dividir tertiary" {{ !$this->puedeDividirMas() ? 'disabled' : '' }} title="Dividir cuenta entre mesas">
                <span class="btn-label">Dividir+</span>
            </button>
        @else
            <button wire:click="mountAction('split_items')" class="pos-quick-action-btn btn-dividir tertiary" {{ !($this->order !== null && count($this->order->orderDetails ?? []) > 0) ? 'disabled' : '' }} title="Dividir cuenta entre mesas">
                <span class="btn-label">Dividir</span>
            </button>
        @endif

        <!-- Transferir -->
        @if(!auth()->user()->hasRole(['waiter']))
            <button wire:click="mountAction('transferOrder')" class="pos-quick-action-btn btn-transferir tertiary" {{ !($this->order && $this->order->table_id && $this->order->status === 'open') ? 'disabled' : '' }} title="Transferir orden a otra mesa">
                <span class="btn-label">Transferir</span>
            </button>
        @endif

        <!-- Liberar Mesa -->
        <button wire:click="mountAction('releaseTable')" class="pos-quick-action-btn btn-liberar" {{ !($this->order && $this->order->table_id) ? 'disabled' : '' }} title="Liberar Mesa">
            <span class="btn-label">Liberar</span>
        </button>

        <!-- Cancelar Pedido -->
        <button wire:click="mountAction('cancelOrder')" class="pos-quick-action-btn btn-cancelar" {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }} title="Cancelar Pedido">
            <span class="btn-label">Cancelar</span>
        </button>
    </div>
</div>
                </div>

                {{-- ITEMS DEL CARRITO --}}
                <div class="pos-cart-items">
                    @forelse($cartItems as $index => $item)
                        <div class="pos-cart-item">
                            <div class="pos-cart-item-header">
                                <div class="pos-cart-item-info">
                                    <div class="pos-cart-item-name-container">
                                        {{-- NOMBRE DEL PRODUCTO --}}
                                        <span class="pos-cart-product-name">{{ $item['name'] }}</span>
                                        
                                        {{-- CONTROLES INLINE COMPACTOS --}}
                                        <div class="pos-inline-controls">
                                            <button 
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                class="pos-btn-minus"
                                                {{ (!$canClearCart || $item['quantity'] <= 1) ? 'disabled' : '' }}
                                                title="Disminuir cantidad"
                                                x-data="{ pressed: false }"
                                                @click="pressed = true; setTimeout(() => pressed = false, 100)"
                                                :class="{ 'pressed': pressed }"
                                            >
                                                −
                                            </button>
                                            
                                            <span class="pos-quantity-display">{{ $item['quantity'] }}</span>
                                            
                                            <button 
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                class="pos-btn-plus"
                                                {{ !$canClearCart ? 'disabled' : '' }}
                                                title="Aumentar cantidad"
                                                x-data="{ pressed: false }"
                                                @click="pressed = true; setTimeout(() => pressed = false, 100)"
                                                :class="{ 'pressed': pressed }"
                                            >
                                                +
                                            </button>
                                        </div>
                                        
                                        {{-- PRECIO FINAL --}}
                                        <span class="pos-final-price">
                                            S/ {{ number_format($item['quantity'] * $item['unit_price'], 2) }}
                                        </span>
                                    </div>
                                    
                                    {{-- MOSTRAR OPCIONES SELECCIONADAS DEBAJO DEL NOMBRE --}}
                                    @if(($item['temperature_selected'] ?? false) || ($item['cooking_point_selected'] ?? false) || ($item['chicken_cut_type_selected'] ?? false))
                                        <div class="pos-selected-option-display">
                                            @if($item['temperature_selected'] ?? false)
                                                <span class="pos-selected-option-tag">Temperatura: {{ $item['temperature'] }}</span>
                                            @endif
                                            
                                            @if($item['cooking_point_selected'] ?? false)
                                                <span class="pos-selected-option-tag">Punto: {{ $item['cooking_point'] }}</span>
                                            @endif
                                            
                                            @if($item['chicken_cut_type_selected'] ?? false)
                                                <span class="pos-selected-option-tag">Presa: {{ $item['chicken_cut_type'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Botón de Eliminación Individual --}}
                                <button
                                    wire:click="removeItem({{ $index }})"
                                    class="pos-item-remove-btn"
                                    {{ !$canClearCart ? 'disabled' : '' }}
                                    title="Eliminar {{ $item['name'] }} del carrito"
                                    x-data="{ pressed: false }"
                                    @click="pressed = true; setTimeout(() => pressed = false, 150)"
                                    :class="{ 'scale-95': pressed }"
                                >
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            {{-- CONTENEDOR DE OPCIONES ESPECIALES - ALTURA ESTANDARIZADA --}}
                            <div class="pos-special-options-container">
                                {{-- OPCIONES ESPECIALES --}} 
                                @if($item['is_cold_drink'] ?? false)
                                    @if(!($item['temperature_selected'] ?? false))
                                        <div class="pos-special-options">
                                            <div class="pos-special-options-title">Temperatura:</div>
                                            <div class="pos-radio-group">
                                                <div class="pos-radio-option">
                                                    <input
                                                        type="radio"
                                                        wire:click="selectTemperature({{ $index }}, 'HELADA')"
                                                        name="temperature-{{ $index }}"
                                                        value="HELADA"
                                                        id="cold-{{ $index }}"
                                                    >
                                                    <label for="cold-{{ $index }}">Helada</label>
                                                </div>
                                                <div class="pos-radio-option">
                                                    <input
                                                        type="radio"
                                                        wire:click="selectTemperature({{ $index }}, 'AL TIEMPO')"
                                                        name="temperature-{{ $index }}"
                                                        value="AL TIEMPO"
                                                        id="room-{{ $index }}"
                                                    >
                                                    <label for="room-{{ $index }}">Al tiempo</label>
                                                </div>
                                                <div class="pos-radio-option">
                                                    <input
                                                        type="radio"
                                                        wire:click="selectTemperature({{ $index }}, 'FRESCA')"
                                                        name="temperature-{{ $index }}"
                                                        value="FRESCA"
                                                        id="fresh-{{ $index }}"
                                                    >
                                                    <label for="fresh-{{ $index }}">Fresca</label>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Opción seleccionada - ocultar completamente las opciones --}}
                                        <div class="pos-special-options-placeholder"></div>
                                    @endif
                                @endif

                                @if($item['is_grill_item'] ?? false)
                                    @if(!($item['cooking_point_selected'] ?? false))
                                        <div class="pos-special-options">
                                            <div class="pos-special-options-title">Punto de cocción:</div>
                                            <div class="pos-radio-group">
                                                @foreach(['Punto Azul', 'Término medio', 'tres cuartos', 'bien cocido'] as $point)
                                                    <div class="pos-radio-option">
                                                        <input
                                                            type="radio"
                                                            wire:click="selectCookingPoint({{ $index }}, '{{ $point }}')"
                                                            name="cooking-point-{{ $index }}"
                                                            value="{{ $point }}"
                                                            id="grill-{{ $index }}-{{ $loop->index }}"
                                                        >
                                                        <label for="grill-{{ $index }}-{{ $loop->index }}">{{ $point }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        {{-- Opción seleccionada - ocultar completamente las opciones --}}
                                        <div class="pos-special-options-placeholder"></div>
                                    @endif
                                @endif

                                @if($item['is_chicken_cut'] ?? false)
                                    @if(!($item['chicken_cut_type_selected'] ?? false))
                                        <div class="pos-special-options">
                                            <div class="pos-special-options-title">Tipo de presa:</div>
                                            <div class="pos-radio-group">
                                                <div class="pos-radio-option">
                                                    <input
                                                        type="radio"
                                                        wire:click="selectChickenCutType({{ $index }}, 'PECHO')"
                                                        name="chicken-cut-{{ $index }}"
                                                        value="PECHO"
                                                        id="chicken-{{ $index }}-breast"
                                                    >
                                                    <label for="chicken-{{ $index }}-breast">Pecho</label>
                                                </div>
                                                <div class="pos-radio-option">
                                                    <input
                                                        type="radio"
                                                        wire:click="selectChickenCutType({{ $index }}, 'PIERNA')"
                                                        name="chicken-cut-{{ $index }}"
                                                        value="PIERNA"
                                                        id="chicken-{{ $index }}-leg"
                                                    >
                                                    <label for="chicken-{{ $index }}-leg">Pierna</label>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Opción seleccionada - ocultar completamente las opciones --}}
                                        <div class="pos-special-options-placeholder"></div>
                                    @endif
                                @endif
                                
                                {{-- ESPACIO RESERVADO PARA MANTENER ALTURA CONSISTENTE --}}
                                @if(!(($item['is_cold_drink'] ?? false) || ($item['is_grill_item'] ?? false) || ($item['is_chicken_cut'] ?? false)))
                                    <div class="pos-special-options-placeholder"></div>
                                @endif
                            </div>
                            


                        </div>
                    @empty
                        <div class="pos-empty-state">
                            <x-heroicon-o-shopping-cart class="pos-empty-icon" />
                            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Carrito vacío</h3>
                            <p style="font-size: 14px;">Selecciona productos para agregar</p>
                        </div>
                    @endforelse
                </div>

                {{-- TOTALES Y ACCIONES --}}
                @if(count($cartItems) > 0)
                    <div class="pos-cart-totals">
                                                 {{-- HEADER CON INFORMACIÓN CONTEXTUAL - OCULTO --}}
                         <div class="pos-totals-header" style="display: none;">
                             <div class="pos-totals-header-title">
                                 @if($order && $order->invoices()->exists())
                                     {{-- Estado: Facturado --}}
                                     <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--pos-success);">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                     </svg>
                                     <span style="color: var(--pos-success);">Facturado</span>
                                 @elseif($order && !$order->invoices()->exists())
                                     {{-- Estado: Orden Guardada --}}
                                     <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--pos-warning);">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                     </svg>
                                     <span style="color: var(--pos-warning);">Orden Pendiente</span>
                                 @elseif($selectedTableId && !$order)
                                     {{-- Estado: Mesa Seleccionada --}}
                                     <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--pos-primary);">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v0H8v0z"></path>
                                     </svg>
                                     <span style="color: var(--pos-primary);">Nueva Orden</span>
                                 @else
                                     {{-- Estado: Venta Directa --}}
                                     <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--pos-secondary);">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                     </svg>
                                     <span style="color: var(--pos-secondary);">Venta Directa</span>
                                 @endif
                             </div>
                             <div class="pos-items-count">
                                 {{ count($cartItems) }} {{ count($cartItems) === 1 ? 'producto' : 'productos' }}
                             </div>
                         </div>

                        {{-- DESGLOSE DE TOTALES --}}
                        <div class="pos-totals-container">
                            <div class="pos-total-row" style="display: none;">
                                <span>Subtotal:</span>
                                <span class="pos-total-amount">S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="pos-total-row" style="display: none;">
                                <span>IGV (18%):</span>
                                <span class="pos-total-amount">S/ {{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="pos-total-row final">
                                <span>
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Total:
                                </span>
                                <span class="pos-total-amount" style="font-size: 13px; font-weight: 700;">S/ {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        {{-- AVISO DE CAJA CERRADA --}}
                        @if(!$this->hasOpenCashRegister)
                            <div style="background: #fee2e2; border: 1px solid #ef4444; border-radius: var(--pos-border-radius); padding: 12px; text-align: center; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; color: #b91c1c; font-weight: 600;">
                                    <x-heroicon-o-exclamation-triangle style="width: 20px; height: 20px;" />
                                    Abra una caja para poder crear órdenes o emitir comprobantes.
                                </div>
                            </div>
                        @endif

                        {{-- BOTONES DE ACCIÓN --}}
                        @if($selectedTableId === null && !$order)
                            @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                <button
                                    wire:click="processOrder"
                                    class="pos-action-btn primary"
                                    {{ (!count($cartItems) || !$this->hasOpenCashRegister) ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-check-circle style="width: 20px; height: 20px;" />
                                    Guardar Orden
                                </button>
                                <button
                                    wire:click="mountAction('processBilling')"
                                    class="pos-action-btn success"
                                    {{ (!count($cartItems) || !$this->hasOpenCashRegister) ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-credit-card style="width: 20px; height: 20px;" />
                                    Emitir Comprobante
                                </button>
                            @endif
                        @elseif(!$order || ($order && !$order->invoices()->exists()))
                            <button
                                wire:click="processOrder"
                                class="pos-action-btn primary"
                                {{ (!count($cartItems) || !$this->hasOpenCashRegister) ? 'disabled' : '' }}
                            >
                                <x-heroicon-m-check-circle style="width: 20px; height: 20px;" />
                                Guardar Orden
                            </button>

                            @if($order && !$order->invoices()->exists() && auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                <button
                                    wire:click="mountAction('processBilling')"
                                    class="pos-action-btn success"
                                    {{ !$this->hasOpenCashRegister ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-credit-card style="width: 20px; height: 20px;" />
                                    Emitir Comprobante
                                </button>
                            @endif
                        @endif

                        @if($order && $order->invoices()->exists())
                            <div style="background: #d1fae5; border: 1px solid #10b981; border-radius: var(--pos-border-radius); padding: 16px; text-align: center; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <x-heroicon-o-check-circle style="width: 24px; height: 24px; color: #059669; margin-right: 8px;" />
                                    <span style="color: #065f46; font-weight: 600;">Orden Facturada</span>
                                </div>
                                <p style="color: #047857; font-size: 14px; margin-bottom: 12px;">
                                    Esta orden ya tiene comprobante(s) emitido(s).
                                </p>
                                @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                    <button
                                        wire:click="reimprimirComprobante"
                                        class="pos-action-btn success"
                                        style="margin-bottom: 0;"
                                    >
                                        <x-heroicon-m-printer style="width: 20px; height: 20px;" />
                                        Reimprimir
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL DE IMPRESIÓN (SIN CAMBIOS) --}}
    <div
        x-data="{
            open: false,
            type: '',
            url: '',
            title: '',
            printProcessing: false,
            init() {
                $wire.on('open-print-modal', (event) => {
                    console.log('Evento recibido:', event);
                    this.type = event.type;
                    this.url = event.url;
                    this.title = event.title;
                    this.open = true;
                });

                if (!window.posInterfacePrintListenerAdded) {
                        window.posInterfacePrintListenerAdded = true;
                        window.lastPrintedInvoiceId = null;
                        window.printStartTime = null;

                        $wire.on('open-print-window', (event) => {
                            if (this.printProcessing) {
                                console.log('⚠️ Impresión en proceso, ignorando duplicado');
                                return;
                            }

                            let invoiceId = Array.isArray(event) ? (event[0]?.id || event[0]) : (event?.id || event);

                            if (!invoiceId) {
                                console.error('❌ Error: ID de comprobante no encontrado');
                                return;
                            }

                            // Prevenir impresión duplicada del mismo comprobante
                            if (window.lastPrintedInvoiceId === invoiceId) {
                                console.log('⚠️ Comprobante ya impreso, evitando duplicado');
                                return;
                            }

                            // Prevenir impresiones muy rápidas (doble clic)
                            const now = Date.now();
                            if (window.printStartTime && (now - window.printStartTime) < 3000) {
                                console.log('⚠️ Impresión muy rápida, ignorando');
                                return;
                            }

                            this.printProcessing = true;
                            window.lastPrintedInvoiceId = invoiceId;
                            window.printStartTime = now;

                            console.log('🖨️ POS Interface - Imprimiendo comprobante...', event);

                            setTimeout(() => {
                                const printUrl = `/print/invoice/${invoiceId}`;
                                console.log('🔗 Abriendo ventana de impresión:', printUrl);
                                const printWindow = window.open(printUrl, 'invoice_print_' + invoiceId, 'width=800,height=600,scrollbars=yes,resizable=yes');

                                // Verificar si la ventana se abrió correctamente
                                if (printWindow && !printWindow.closed) {
                                    console.log('✅ Ventana de impresión abierta exitosamente');
                                } else {
                                    console.error('❌ Error al abrir ventana de impresión');
                                }

                                // Resetear más rápidamente para futuras impresiones
                                setTimeout(() => {
                                    this.printProcessing = false;
                                }, 1000);
                            }, 500);
                        });
                    }
            }
        }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div
                @click="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                @click.outside="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <x-heroicon-o-check-circle class="h-10 w-10 text-green-500 mx-auto"/>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="title"></h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                El comprobante se ha procesado exitosamente. ¿Desea imprimirlo???
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button
                        @click="window.open(url, '_blank'); open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Imprimir
                    </button>
                    <button
                        @click="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Saltar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product-images.css') }}">
@endpush

<script>
// Listener para redirección automática al mapa de mesas después de imprimir comprobantes
window.addEventListener('message', function(event) {
    console.log('🖨️ POS Interface - Evento recibido:', event.data);

    if (event.data === 'invoice-completed' ||
        (event.data && event.data.type === 'invoice-completed')) {

        console.log('✅ Comprobante impreso - Redirigiendo al mapa de mesas');

        setTimeout(function() {
            console.log('🔄 Redirigiendo al mapa de mesas...');
            window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';
        }, 1500);
    }
});
</script>
