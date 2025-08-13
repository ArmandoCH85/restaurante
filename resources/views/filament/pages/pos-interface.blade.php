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
            --pos-cart-width: clamp(280px, 25vw, 350px);
            --pos-sidebar-width: clamp(140px, 20vw, 200px);
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
            --pos-product-min-width: 100px;
            --pos-product-max-width: 150px;
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

        /* SIDEBAR CATEGORÍAS RESPONSIVO */
        .pos-categories {
            grid-area: sidebar;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--pos-shadow);
        }

        .pos-categories-header {
            padding: 12px 8px 8px;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            background: white;
            margin: 6px;
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
            padding: 8px 6px;
        }

        /* BOTONES DE CATEGORÍA OPTIMIZADOS PARA 1024x637 AL 100% ZOOM */
        .pos-category-btn {
            width: 100%;
            padding: 6px 8px;
            margin-bottom: 3px;
            text-align: left;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            background: white;
            color: #64748b;
            font-weight: 700; /* Más negrito para pantallas POS */
            font-size: 13px;  /* Un poco más grande para mejor legibilidad */
            text-transform: uppercase; /* Mostrar categorías en MAYÚSCULAS */
            transition: var(--pos-transition);
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .pos-category-btn:hover {
            background: #f3f4f6;
            border-color: var(--pos-border-focus);
            transform: translateY(-1px);
            box-shadow: var(--pos-shadow);
        }

        .pos-category-btn.active {
            background: #64748b;
            color: white;
            border: 2px solid var(--pos-border-focus);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
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

        .pos-search-bar {
            padding: 10px;
            background: white;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 6px;
            position: relative;
        }

        .pos-search-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .pos-search-icon {
            position: absolute;
            left: clamp(8px, 1.5vw, 12px);
            top: 50%;
            transform: translateY(-50%);
            width: clamp(14px, 2vw, 18px);
            height: clamp(14px, 2vw, 18px);
            color: var(--pos-gray-400);
            pointer-events: none;
            z-index: 2;
        }

        .pos-search-input {
            width: 100%;
            padding: clamp(6px, 1.5vw, 12px) clamp(35px, 4vw, 45px) clamp(6px, 1.5vw, 12px) clamp(35px, 4vw, 45px);
            border: 1px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            font-size: clamp(12px, 2vw, 16px);
            transition: var(--pos-transition);
            background: white;
            /* Mejorar en móviles */
            -webkit-appearance: none;
            appearance: none;
            touch-action: manipulation;
        }

        .pos-search-input:focus {
            outline: none;
            border-color: var(--pos-primary);
            background: white;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .pos-search-clear {
            position: absolute;
            right: clamp(8px, 1.5vw, 12px);
            top: 50%;
            transform: translateY(-50%);
            width: clamp(20px, 2.5vw, 24px);
            height: clamp(20px, 2.5vw, 24px);
            border: none;
            background: var(--pos-gray-300);
            color: var(--pos-gray-600);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(12px, 1.8vw, 16px);
            font-weight: bold;
            transition: var(--pos-transition);
            z-index: 2;
            opacity: 0;
            visibility: hidden;
        }

        .pos-search-clear.show {
            opacity: 1;
            visibility: visible;
        }

        .pos-search-clear:hover {
            background: var(--pos-gray-400);
            color: white;
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

        .pos-products-grid {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 6px;
            background: white;
        }

        /* GRID DE PRODUCTOS RESPONSIVO */
        .pos-products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(var(--pos-product-min-width), 1fr));
            gap: var(--pos-gap);
            flex: 1;
            overflow-y: auto;
            padding: var(--pos-gap);
            padding-bottom: calc(var(--pos-gap) * 2);
        }

        /* CARDS DE PRODUCTOS RESPONSIVAS */
        .pos-product-card {
            background: white;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            padding: clamp(4px, 1vw, 8px);
            text-align: center;
            cursor: pointer;
            transition: var(--pos-transition-fast);
            position: relative;
            overflow: hidden;
            min-height: clamp(70px, 10vw, 100px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Mejorar experiencia táctil */
            touch-action: manipulation;
            user-select: none;
        }

        .pos-product-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--pos-shadow-hover);
            border-color: var(--pos-border-focus);
            background: var(--pos-surface);
        }

        .pos-product-card:active {
            transform: translateY(0);
        }

        .pos-product-image {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            margin: 0 auto 4px;
            background: var(--pos-gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .pos-product-name {
            font-size: 12px;           /* Un poco más grande */
            font-weight: 700;          /* Más negrito */
            text-transform: uppercase; /* MAYÚSCULAS para mejor lectura en POS */
            color: #1f2937;
            margin-bottom: 3px;
            line-height: 1.2;
            min-height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .pos-product-price {
            font-size: 12px;
            font-weight: 700;
            color: var(--pos-success);
            margin-top: auto;
            padding-top: 2px;
        }

        /* CARRITO RESPONSIVO */
        .pos-cart {
            grid-area: cart;
            background: white;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--pos-shadow);
        }

        .pos-cart-header {
            padding: 10px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 6px;
        }

        .pos-cart-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .pos-cart-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 6px;
            align-items: end;
        }

        /* ACCIONES RÁPIDAS COMPACTAS - 4 POR FILA */
        .pos-quick-actions {
            margin-top: 8px;
            padding: 8px;
            background: white;
            border-radius: var(--pos-border-radius);
            border: 1px solid var(--pos-gray-200);
        }

        .pos-quick-actions-title {
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            text-align: center;
        }

        .pos-quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3px;
        }

        .pos-quick-action-btn {
            padding: clamp(8px, 1.5vw, 12px) clamp(4px, 1vw, 6px);
            border: 1px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            background: white;
            color: #6b7280;
            font-size: clamp(9px, 1.5vw, 11px);
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: var(--pos-transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: clamp(2px, 0.5vw, 4px);
            position: relative;
            min-height: clamp(36px, 5vw, 44px);
            height: clamp(36px, 5vw, 44px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pos-quick-action-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            filter: brightness(1.1);
        }

        .pos-quick-action-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* JERARQUÍA VISUAL - ACCIONES PRIMARIAS */
        .pos-quick-action-btn.primary {
            min-height: clamp(44px, 6vw, 52px);
            height: clamp(44px, 6vw, 52px);
            font-weight: 600;
            font-size: clamp(10px, 1.8vw, 12px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            border-width: 2px;
        }

        .pos-quick-action-btn.primary .pos-quick-action-icon {
            width: clamp(16px, 2.5vw, 20px);
            height: clamp(16px, 2.5vw, 20px);
        }

        /* JERARQUÍA VISUAL - ACCIONES SECUNDARIAS */
        .pos-quick-action-btn.secondary {
            min-height: clamp(40px, 5.5vw, 48px);
            height: clamp(40px, 5.5vw, 48px);
            font-weight: 550;
            font-size: clamp(9px, 1.6vw, 11px);
        }

        .pos-quick-action-btn.secondary .pos-quick-action-icon {
            width: clamp(14px, 2.2vw, 18px);
            height: clamp(14px, 2.2vw, 18px);
        }

        /* JERARQUÍA VISUAL - ACCIONES TERCIARIAS */
        .pos-quick-action-btn.tertiary {
            min-height: clamp(36px, 5vw, 42px);
            height: clamp(36px, 5vw, 42px);
            opacity: 0.9;
            font-size: clamp(8px, 1.4vw, 10px);
        }

        .pos-quick-action-btn.tertiary .pos-quick-action-icon {
            width: clamp(12px, 2vw, 16px);
            height: clamp(12px, 2vw, 16px);
        }

        /* COLORES OPTIMIZADOS CON JERARQUÍA VISUAL */
        .pos-quick-action-btn.btn-mapa {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-color: #1e40af;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.3);
        }

        .pos-quick-action-btn.btn-comanda {
            background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
            border-color: #c2410c;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(194, 65, 12, 0.3);
        }

        .pos-quick-action-btn.btn-precuenta {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            border-color: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.3);
        }

        .pos-quick-action-btn.btn-reabrir {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
            border-color: #15803d;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(21, 128, 61, 0.3);
        }

        .pos-quick-action-btn.btn-dividir {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            border-color: #7c3aed;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(124, 58, 237, 0.3);
        }

        .pos-quick-action-btn.btn-transferir {
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
            border-color: #4338ca;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(67, 56, 202, 0.3);
        }

        .pos-quick-action-btn.btn-liberar {
            background: linear-gradient(135deg, #475569 0%, #64748b 100%);
            border-color: #475569;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(71, 85, 105, 0.3);
        }

        .pos-quick-action-btn.btn-cancelar {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            border-color: #dc2626;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }

        .pos-quick-action-btn:disabled {
            background: var(--pos-gray-200) !important;
            border-color: var(--pos-gray-300) !important;
            color: var(--pos-gray-400) !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
            filter: none !important;
            opacity: 0.6;
        }

        .pos-quick-action-btn:disabled .pos-quick-action-icon {
            opacity: 0.5;
        }

        .pos-quick-action-icon {
            width: clamp(12px, 2vw, 16px);
            height: clamp(12px, 2vw, 16px);
            flex-shrink: 0;
            transition: var(--pos-transition);
        }

        /* ETIQUETAS DE TEXTO */
        .pos-quick-action-btn .btn-label {
            font-size: clamp(8px, 1.2vw, 10px);
            font-weight: 500;
            line-height: 1;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .pos-quick-action-btn.primary .btn-label {
            font-size: clamp(9px, 1.4vw, 11px);
            font-weight: 600;
        }

        .pos-quick-action-btn.secondary .btn-label {
            font-size: clamp(8px, 1.3vw, 10px);
            font-weight: 550;
        }

        .pos-quick-action-btn.tertiary .btn-label {
            font-size: clamp(7px, 1.1vw, 9px);
        }

        /* ITEMS DEL CARRITO OPTIMIZADOS PARA 1024x637 AL 100% ZOOM */
        .pos-cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 4px;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 4px;
            background: white;
            max-height: calc(600px - 250px);
            min-height: 200px;
        }

        .pos-cart-item {
            background: var(--pos-gray-50);
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            padding: 6px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .pos-cart-item:hover {
            background: white;
            box-shadow: var(--pos-shadow);
        }

        .pos-cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            gap: clamp(6px, 1vw, 8px);
        }

        .pos-cart-item-info {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-right: clamp(6px, 1vw, 8px);
        }

        .pos-cart-item-name {
            font-size: clamp(12px, 2vw, 14px);
            font-weight: 600;
            color: #1f2937;
            flex: 1;
            margin-right: 8px;
            line-height: 1.3;
        }

        .pos-cart-item-price {
            font-size: clamp(11px, 1.8vw, 13px);
            color: #6b7280;
            white-space: nowrap;
        }

        /* BOTÓN DE ELIMINACIÓN INDIVIDUAL */
        .pos-item-remove-btn {
            width: clamp(28px, 4vw, 32px);
            height: clamp(28px, 4vw, 32px);
            border: none;
            border-radius: var(--pos-border-radius);
            background: var(--pos-danger);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--pos-transition);
            font-size: clamp(12px, 2vw, 14px);
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(239, 68, 68, 0.2);
        }

        .pos-item-remove-btn:hover:not(:disabled) {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }

        .pos-item-remove-btn:active:not(:disabled) {
            transform: scale(0.95);
            transition: transform 0.1s ease;
        }

        .pos-item-remove-btn:disabled {
            background: var(--pos-gray-300);
            color: var(--pos-gray-500);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .pos-item-remove-btn:focus {
            outline: 2px solid var(--pos-danger);
            outline-offset: 2px;
        }

        .pos-item-remove-btn:focus:not(:focus-visible) {
            outline: none;
        }

        /* CONTROLES DE CANTIDAD OPTIMIZADOS PARA TOUCH */
        .pos-quantity-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: clamp(8px, 1.5vw, 12px);
            border-radius: var(--pos-border-radius);
            border: 1px solid var(--pos-gray-200);
            gap: clamp(8px, 1.5vw, 12px);
        }

        .pos-quantity-btn {
            width: clamp(40px, 5vw, 48px);
            height: clamp(40px, 5vw, 48px);
            border: none;
            border-radius: var(--pos-border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--pos-transition);
            font-weight: 600;
            font-size: clamp(16px, 2.5vw, 20px);
            position: relative;
            overflow: hidden;
        }

        /* DIFERENCIACIÓN POR FUNCIÓN */
        .pos-quantity-btn.decrease {
            background: var(--pos-danger);
            color: white;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }

        .pos-quantity-btn.increase {
            background: var(--pos-success);
            color: white;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        /* ESTADOS INTERACTIVOS MEJORADOS */
        .pos-quantity-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .pos-quantity-btn.decrease:hover:not(:disabled) {
            background: #dc2626;
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        .pos-quantity-btn.increase:hover:not(:disabled) {
            background: #059669;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }

        /* FEEDBACK TÁCTIL - ESTADO ACTIVO */
        .pos-quantity-btn:active:not(:disabled) {
            transform: scale(0.95);
            transition: transform 0.1s ease;
        }

        /* ESTADOS DISABLED MEJORADOS */
        .pos-quantity-btn:disabled {
            background: var(--pos-gray-200);
            color: var(--pos-gray-400);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .pos-quantity-btn:disabled svg {
            opacity: 0.5;
        }

        .pos-quantity-value {
            font-size: clamp(16px, 2.5vw, 20px);
            font-weight: 700;
            color: var(--pos-gray-600);
            min-width: clamp(45px, 6vw, 55px);
            text-align: center;
            background: var(--pos-gray-50);
            border-radius: var(--pos-border-radius);
            padding: clamp(8px, 1.5vw, 12px) clamp(6px, 1vw, 8px);
            border: 1px solid var(--pos-gray-200);
            transition: var(--pos-transition);
            position: relative;
        }

        /* ANIMACIÓN DE CAMBIO DE CANTIDAD */
        .pos-quantity-value.updating {
            background: var(--pos-primary);
            color: white;
            transform: scale(1.05);
            border-color: var(--pos-primary);
        }

        .pos-quantity-total {
            font-size: clamp(14px, 2.2vw, 16px);
            font-weight: 700;
            color: var(--pos-success);
            background: rgba(16, 185, 129, 0.1);
            padding: clamp(4px, 1vw, 6px) clamp(8px, 1.5vw, 10px);
            border-radius: var(--pos-border-radius);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* INDICADORES DE LÍMITES */
        .pos-quantity-controls.at-minimum .pos-quantity-btn.decrease {
            background: var(--pos-gray-300);
            color: var(--pos-gray-500);
            cursor: not-allowed;
            box-shadow: none;
        }

        .pos-quantity-controls.at-minimum .pos-quantity-btn.decrease:hover {
            transform: none;
            background: var(--pos-gray-300);
        }

        /* EFECTOS DE RIPPLE PARA FEEDBACK TÁCTIL */
        .pos-quantity-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }

        .pos-quantity-btn:active::before {
            width: 100%;
            height: 100%;
        }

        /* MEJORAS DE ACCESIBILIDAD */
        .pos-quantity-btn:focus {
            outline: 2px solid var(--pos-primary);
            outline-offset: 2px;
        }

        .pos-quantity-btn:focus:not(:focus-visible) {
            outline: none;
        }

        /* INDICADOR DE CARGA PARA ACTUALIZACIONES */
        .pos-quantity-controls.updating {
            pointer-events: none;
            opacity: 0.7;
        }

        .pos-quantity-controls.updating .pos-quantity-btn {
            cursor: wait;
        }

        /* RESPONSIVE PARA MÓVILES */
        @media (max-width: 767px) {
            .pos-quantity-controls {
                padding: 6px;
                gap: 6px;
            }

            .pos-quantity-btn {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .pos-quantity-value {
                min-width: 40px;
                font-size: 16px;
                padding: 6px 4px;
            }

            .pos-quantity-total {
                font-size: 13px;
                padding: 3px 6px;
            }
        }

        /* RESPONSIVE PARA TABLETS */
        @media (min-width: 768px) and (max-width: 1023px) {
            .pos-quantity-btn {
                width: 42px;
                height: 42px;
            }

            .pos-quantity-value {
                min-width: 48px;
                font-size: 18px;
            }

            .pos-item-remove-btn {
                width: 30px;
                height: 30px;
            }
        }

        /* RESPONSIVE ESPECÍFICO PARA BOTÓN DE ELIMINACIÓN */
        @media (max-width: 767px) {
            .pos-item-remove-btn {
                width: 26px;
                height: 26px;
                font-size: 11px;
            }

            .pos-cart-item-header {
                gap: 4px;
            }

            .pos-cart-item-info {
                margin-right: 4px;
            }

            .pos-cart-item-name {
                font-size: 11px;
            }

            .pos-cart-item-price {
                font-size: 10px;
            }
        }

        /* ANIMACIÓN DE ELIMINACIÓN */
        .pos-cart-item.removing {
            animation: slideOut 0.3s ease-out forwards;
            pointer-events: none;
        }

        @keyframes slideOut {
            0% {
                opacity: 1;
                transform: translateX(0);
                max-height: 200px;
            }
            50% {
                opacity: 0.5;
                transform: translateX(-20px);
            }
            100% {
                opacity: 0;
                transform: translateX(-100%);
                max-height: 0;
                margin: 0;
                padding: 0;
            }
        }

        /* ========================================= */
        /* MODAL DE COMANDA - DISEÑO PROFESIONAL */
        /* ========================================= */

        /* OVERLAY DEL MODAL */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-window {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* CONTENEDOR PRINCIPAL DEL MODAL */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--pos-gray-200);
            max-width: 500px;
            width: 90vw;
            animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* HEADER DEL MODAL - minimal y neutro */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-header {
            background: #ffffff;
            color: #111827;
            padding: 12px 16px;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid var(--pos-gray-200);
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-modal-heading {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }

    .fi-modal[data-modal-id*="printComanda"] .fi-modal-description { display: none; }

        /* BOTÓN CERRAR */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-close-btn {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 8px;
            transition: var(--pos-transition);
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        /* CUERPO DEL MODAL */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-body {
            padding: 16px;
            background: white;
        }

        /* FORMULARIO */
        .fi-modal[data-modal-id*="printComanda"] .fi-form {
            margin: 0;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-input-wrapper {
            background: var(--pos-gray-50);
            border: 2px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            transition: var(--pos-transition);
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-input-wrapper:focus-within {
            border-color: #c2410c;
            background: white;
            box-shadow: 0 0 0 3px rgba(194, 65, 12, 0.1);
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-input {
            font-size: 15px;
            padding: 12px 14px;
            border: none;
            background: transparent;
            font-weight: 500;
            line-height: 1.4;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-input:focus {
            outline: none;
            box-shadow: none;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-field-wrp-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--pos-gray-600);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            line-height: 1.3;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-field-wrp-hint {
            font-size: 13px;
            color: var(--pos-gray-400);
            margin-top: 6px;
            font-style: italic;
            line-height: 1.4;
        }

        /* ESTADO VACÍO DEL MODAL */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-body:empty::before {
            content: "✅ Listo para generar comanda";
            display: block;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: var(--pos-success);
            padding: 20px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px dashed var(--pos-success);
            border-radius: var(--pos-border-radius);
            margin: 0;
            line-height: 1.4;
        }

        /* FOOTER DEL MODAL - COMPACTO */
        .fi-modal[data-modal-id*="printComanda"] .fi-modal-footer {
            background: var(--pos-gray-50);
            border-top: 1px solid var(--pos-gray-200);
            padding: 16px 20px;
            border-radius: 0 0 12px 12px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            align-items: center;
        }

        /* BOTONES DEL MODAL - TAMAÑO COMPACTO */
        .fi-modal[data-modal-id*="printComanda"] .fi-btn {
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            padding: 8px 12px;
            transition: var(--pos-transition);
            display: flex;
            align-items: center;
            gap: 4px;
            height: 32px;
            min-width: 100px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            line-height: 1.2;
            border: 1px solid transparent;
        }

        /* BOTÓN CONFIRMAR - COMPACTO */
        .fi-modal[data-modal-id*="printComanda"] .fi-btn-primary {
            background: #ea580c;
            border-color: #ea580c;
            color: white;
            font-weight: 600;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-btn-primary:hover {
            background: #dc2626;
            border-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(234, 88, 12, 0.25);
        }

        /* BOTÓN IMPRIMIR - COMPACTO */
        .fi-modal[data-modal-id*="printComanda"] .fi-btn:has([data-icon="printer"]) {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-btn:has([data-icon="printer"]):hover {
            background: #2563eb;
            border-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
        }

        /* BOTÓN DESCARGAR - COMPACTO */
        .fi-modal[data-modal-id*="printComanda"] .fi-btn:has([data-icon="download"]) {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-btn:has([data-icon="download"]):hover {
            background: #059669;
            border-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        }

        /* BOTÓN CANCELAR - COMPACTO */
        .fi-modal[data-modal-id*="printComanda"] .fi-btn-secondary {
            background: white;
            border-color: #d1d5db;
            color: #6b7280;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #374151;
        }

        /* RESPONSIVE PARA MODAL DE COMANDA */
        @media (max-width: 767px) {
            .fi-modal[data-modal-id*="printComanda"] .fi-modal-content {
                width: 95vw;
                max-width: none;
                margin: 10px;
                border-radius: 8px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-modal-header {
                padding: 18px 20px;
                border-radius: 8px 8px 0 0;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-modal-heading {
                font-size: 18px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-modal-description {
                font-size: 13px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-modal-body {
                padding: 20px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-modal-footer {
                padding: 16px 18px;
                flex-direction: column;
                gap: 8px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-btn {
                width: 100%;
                justify-content: center;
                height: 36px;
                font-size: 13px;
                min-width: auto;
            }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .fi-modal[data-modal-id*="printComanda"] .fi-modal-content {
                width: 85vw;
                max-width: 480px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-modal-footer {
                flex-wrap: wrap;
                gap: 8px;
            }

            .fi-modal[data-modal-id*="printComanda"] .fi-btn {
                flex: 0 1 auto;
                min-width: 95px;
                height: 34px;
            }
        }

        /* ANIMACIONES SUTILES */
        .fi-modal[data-modal-id*="printComanda"] .fi-btn:active {
            transform: scale(0.98);
            transition: transform 0.1s ease;
        }

        .fi-modal[data-modal-id*="printComanda"] .fi-input-wrapper:focus-within {
            transform: translateY(-1px);
        }

        /* OPCIONES ESPECIALES MEJORADAS */
        .pos-special-options {
            margin: 12px 0;
            padding: 12px;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
        }

        .pos-special-options-title {
            font-size: 12px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
        }

        .pos-radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pos-radio-option {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pos-radio-option label {
            font-size: 11px;
            color: #374151;
            cursor: pointer;
        }

        /* TOTALES DEL CARRITO MEJORADOS - UX OPTIMIZADO */
        .pos-cart-totals {
            padding: clamp(12px, 2vw, 16px);
            background: white;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 6px;
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
            padding: clamp(12px, 2vw, 16px);
            border-radius: var(--pos-border-radius);
            margin-bottom: clamp(8px, 1.5vw, 12px);
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

        .pos-total-row:last-child {
            margin-bottom: 0;
        }

        .pos-total-row.final {
            padding-top: clamp(8px, 1.5vw, 12px);
            border-top: 2px solid var(--pos-primary);
            font-weight: 700;
            font-size: clamp(16px, 2.8vw, 18px);
            color: var(--pos-success);
            background: rgba(16, 185, 129, 0.05);
            margin: clamp(8px, 1.5vw, 12px) -12px -12px -12px;
            padding-left: clamp(12px, 2vw, 16px);
            padding-right: clamp(12px, 2vw, 16px);
            border-radius: 0 0 var(--pos-border-radius) var(--pos-border-radius);
        }

        .pos-total-amount {
            font-weight: 600;
        }

        .pos-total-row.final .pos-total-amount {
            font-weight: 800;
            font-size: clamp(17px, 3vw, 20px);
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
                padding: 10px;
            }

            .pos-totals-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .pos-items-count {
                align-self: flex-end;
            }

            .pos-total-row.final {
                margin: 8px -10px -10px -10px;
                padding-left: 10px;
                padding-right: 10px;
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

        /* LOGO DEL SISTEMA - ESTADO INICIAL */
        .pos-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 400px;
            padding: 40px 20px;
            text-align: center;
        }

        .pos-system-logo {
            width: 200px;
            height: auto;
            max-width: 80%;
            margin-bottom: 24px;
            opacity: 0.9;
            transition: all 0.3s ease;
        }

        .pos-system-logo:hover {
            opacity: 1;
            transform: scale(1.02);
        }

        .pos-logo-text {
            font-size: 16px;
            font-weight: 500;
            color: #6b7280;
            margin: 0;
            opacity: 0.8;
        }

        /* ESTADO VACÍO */
        .pos-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
            color: #6b7280;
        }

        .pos-empty-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* BREAKPOINTS RESPONSIVOS MEJORADOS */

        /* Pantallas grandes (1400px+) */
        @media (min-width: 1400px) {
            :root {
                --pos-cart-width: 350px;
                --pos-sidebar-width: 200px;
                --pos-product-min-width: 140px;
                --pos-gap: 8px;
            }
        }

        /* Pantallas medianas (1024px - 1399px) */
        @media (max-width: 1399px) and (min-width: 1024px) {
            :root {
                --pos-cart-width: 320px;
                --pos-sidebar-width: 180px;
                --pos-product-min-width: 120px;
                --pos-gap: 6px;
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Tablets (768px - 1023px) */
        @media (max-width: 1023px) and (min-width: 768px) {
            :root {
                --pos-cart-width: 280px;
                --pos-sidebar-width: 160px;
                --pos-product-min-width: 100px;
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
                font-size: 7px;
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
                    <h3 class="text-sm font-bold text-gray-800 text-center">Categorías</h3>
                </div>

                <div class="pos-categories-content">
                    @foreach($this->getCategoriesProperty() as $category)
                        <button
                            wire:click="selectCategory({{ $category->id }})"
                            class="pos-category-btn {{ $selectedCategoryId === $category->id ? 'active' : '' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                {{-- SUBCATEGORÍAS --}}
                @if($selectedCategoryId && $subcategories->isNotEmpty())
                    <div class="border-t border-gray-200 p-3">
                        <h4 class="text-xs font-semibold text-gray-600 mb-2">Subcategorías</h4>
                        <div class="space-y-1">
                            <button
                                wire:click="selectSubcategory(null)"
                                class="pos-category-btn {{ $selectedSubcategoryId === null ? 'active' : '' }}"
                                style="font-size: 12px; padding: 8px 12px;"
                            >
                                Todos
                            </button>
                            @foreach($subcategories as $subcat)
                                <button
                                    wire:click="selectSubcategory({{ $subcat->id }})"
                                    class="pos-category-btn {{ $selectedSubcategoryId === $subcat->id ? 'active' : '' }}"
                                    style="font-size: 12px; padding: 8px 12px;"
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
                                    <div class="pos-product-image">
                                        @if($product->image_path)
                                            <img
                                                src="{{ $product->image }}"
                                                alt="{{ $product->name }}"
                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;"
                                            />
                                        @else
                                            <span style="font-weight: bold; color: #9ca3af; font-size: 18px;">
                                                {{ strtoupper(substr($product->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="pos-product-name">{{ $product->name }}</div>
                                    <div class="pos-product-price">S/ {{ number_format($product->sale_price, 2) }}</div>

                                    @if($product->category)
                                        <div style="margin-top: 8px;">
                                            <span style="background: #e5e7eb; color: #374151; padding: 2px 8px; border-radius: 12px; font-size: 10px;">
                                                {{ $product->category->name }}
                                            </span>
                                        </div>
                                    @endif
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
                    {{-- TÍTULO Y CONTROLES EN LA MISMA LÍNEA --}}
                    <div class="pos-cart-title" style="display: flex; justify-content: space-around; align-items: center; padding: 0 8px;">
                        <span style="background: var(--pos-success); color: white; padding: 4px 12px; border-radius: 16px; font-size: 14px; font-weight: 600;">
                            {{ count($cartItems) }} items
                        </span>

                        {{-- CONTROLES CENTRADOS --}}
                        <div style="display: flex; align-items: center; gap: 16px;">
                            {{-- INFORMACIÓN DE LA MESA --}}
                            @if($selectedTable)
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <x-heroicon-s-home style="width: 14px; height: 14px; color: #059669;" />
                                    <span style="font-size: 12px; font-weight: 600; color: #000000;">
                                        Mesa {{ $selectedTable->number }}
                                    </span>
                                </div>
                            @endif

                            <div>
                                <label style="display: block; font-size: 10px; font-weight: 600; color: #374151; margin-bottom: 2px;">
                                    Comensales <span style="color: #ef4444;">*</span>
                                </label>
                                <div style="position: relative;">
                                    <x-heroicon-s-users style="position: absolute; left: 4px; top: 50%; transform: translateY(-50%); width: 12px; height: 12px; color: #9ca3af;" />
                                    <input
                                        type="number"
                                        wire:model.live="numberOfGuests"
                                        min="1"
                                        style="width: 60px; padding: 4px 4px 4px 18px; border: 1px solid var(--pos-gray-200); border-radius: 4px; text-align: center; font-weight: 600; font-size: 12px;"
                                        placeholder="0"
                                        required
                                    >
                                </div>
                            </div>

                            <button
                                wire:click="clearCart"
                                style="width: 32px; height: 32px; border: 1px solid #fca5a5; border-radius: 6px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; margin-top: 16px;"
                                title="Limpiar carrito"
                                {{ !$canClearCart ? 'disabled' : '' }}
                                onmouseover="this.style.background='#fee2e2'"
                                onmouseout="this.style.background='#fef2f2'"
                            >
                                <x-heroicon-s-trash style="width: 16px; height: 16px;" />
                            </button>
                        </div>
                    </div>

                    {{-- ACCIONES RÁPIDAS REORGANIZADAS --}}
                    <div class="pos-quick-actions">
                        <div class="pos-quick-actions-title">Acciones Rápidas</div>
                        <div class="pos-quick-actions-grid">
                            {{-- ACCIONES PRIMARIAS - Más frecuentes --}}
                            {{-- Mapa --}}
                            <button
                                wire:click="mountAction('backToTableMap')"
                                class="pos-quick-action-btn btn-mapa primary"
                                title="Ir al mapa de mesas"
                            >
                                <x-heroicon-o-map class="pos-quick-action-icon" />
                                <span class="btn-label">Mapa</span>
                            </button>

                            {{-- Comanda --}}
                            <button
                                wire:click="mountAction('printComanda')"
                                class="pos-quick-action-btn btn-comanda primary"
                                {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }}
                                title="Imprimir comanda para cocina"
                            >
                                <x-heroicon-o-document-text class="pos-quick-action-icon" />
                                <span class="btn-label">Comanda</span>
                            </button>

                            {{-- ACCIONES SECUNDARIAS - Uso moderado --}}
                            {{-- Pre-Cuenta --}}
                            <button
                                wire:click="mountAction('printPreBillNew')"
                                class="pos-quick-action-btn btn-precuenta secondary"
                                {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }}
                                title="Imprimir pre-cuenta para cliente"
                            >
                                <x-heroicon-o-document-duplicate class="pos-quick-action-icon" />
                                <span class="btn-label">Pre-Cuenta</span>
                            </button>

                            {{-- Reabrir --}}
                            <button
                                wire:click="mountAction('reopen_order_for_editing')"
                                class="pos-quick-action-btn btn-reabrir secondary"
                                {{ !($this->order instanceof \App\Models\Order && !$this->order->invoices()->exists()) ? 'disabled' : '' }}
                                title="Reabrir orden para edición"
                            >
                                <x-heroicon-o-lock-open class="pos-quick-action-icon" />
                                <span class="btn-label">Reabrir</span>
                            </button>

                            {{-- ACCIONES TERCIARIAS - Uso ocasional --}}
                            {{-- Dividir --}}
                            <button
                                wire:click="mountAction('split_items')"
                                class="pos-quick-action-btn btn-dividir tertiary"
                                {{ !($this->order !== null && count($this->order->orderDetails ?? []) > 0) ? 'disabled' : '' }}
                                title="Dividir cuenta entre mesas"
                            >
                                <x-heroicon-o-scissors class="pos-quick-action-icon" />
                                <span class="btn-label">Dividir</span>
                            </button>

                            {{-- Transferir --}}
                            @if(!auth()->user()->hasRole(['waiter', 'cashier']))
                                <button
                                    wire:click="mountAction('transferOrder')"
                                    class="pos-quick-action-btn btn-transferir tertiary"
                                    {{ !($this->order && $this->order->table_id && $this->order->status === 'open') ? 'disabled' : '' }}
                                    title="Transferir orden a otra mesa"
                                >
                                    <x-heroicon-o-arrow-path-rounded-square class="pos-quick-action-icon" />
                                    <span class="btn-label">Transferir</span>
                                </button>
                            @endif

                            {{-- Liberar Mesa --}}
                            <button
                                wire:click="mountAction('releaseTable')"
                                class="pos-quick-action-btn btn-liberar"
                                {{ !($this->order && $this->order->table_id) ? 'disabled' : '' }}
                                title="Liberar Mesa"
                            >
                                <x-heroicon-o-home class="pos-quick-action-icon" />
                            </button>

                            {{-- Cancelar Pedido --}}
                            <button
                                wire:click="mountAction('cancelOrder')"
                                class="pos-quick-action-btn btn-cancelar"
                                {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }}
                                title="Cancelar Pedido"
                            >
                                <x-heroicon-o-x-circle class="pos-quick-action-icon" />
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
                                    <div class="pos-cart-item-name">{{ $item['name'] }}</div>
                                    <div class="pos-cart-item-price">S/ {{ number_format($item['unit_price'], 2) }} c/u</div>
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

                            {{-- OPCIONES ESPECIALES --}}
                            @if($item['is_cold_drink'] ?? false)
                                <div class="pos-special-options">
                                    <div class="pos-special-options-title">Temperatura:</div>
                                    <div class="pos-radio-group">
                                        <div class="pos-radio-option">
                                            <input
                                                type="radio"
                                                wire:model.live="cartItems.{{ $index }}.temperature"
                                                value="HELADA"
                                                id="cold-{{ $index }}"
                                            >
                                            <label for="cold-{{ $index }}">Helada</label>
                                        </div>
                                        <div class="pos-radio-option">
                                            <input
                                                type="radio"
                                                wire:model.live="cartItems.{{ $index }}.temperature"
                                                value="AL TIEMPO"
                                                id="room-{{ $index }}"
                                            >
                                            <label for="room-{{ $index }}">Al tiempo</label>
                                        </div>
                                        <div class="pos-radio-option">
                                            <input
                                                type="radio"
                                                wire:model.live="cartItems.{{ $index }}.temperature"
                                                value="FRESCA"
                                                id="fresh-{{ $index }}"
                                            >
                                            <label for="fresh-{{ $index }}">Fresca</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($item['is_grill_item'] ?? false)
                                <div class="pos-special-options">
                                    <div class="pos-special-options-title">Punto de cocción:</div>
                                    <div class="pos-radio-group">
                                        @foreach(['ROJO', 'JUGOSO', 'TRES CUARTOS', 'BIEN COCIDO'] as $point)
                                            <div class="pos-radio-option">
                                                <input
                                                    type="radio"
                                                    wire:model.live="cartItems.{{ $index }}.cooking_point"
                                                    value="{{ $point }}"
                                                    id="grill-{{ $index }}-{{ $loop->index }}"
                                                >
                                                <label for="grill-{{ $index }}-{{ $loop->index }}">{{ $point }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($item['is_chicken_cut'] ?? false)
                                <div class="pos-special-options">
                                    <div class="pos-special-options-title">Tipo de presa:</div>
                                    <div class="pos-radio-group">
                                        <div class="pos-radio-option">
                                            <input
                                                type="radio"
                                                wire:model.live="cartItems.{{ $index }}.chicken_cut_type"
                                                value="PECHO"
                                                id="chicken-{{ $index }}-breast"
                                            >
                                            <label for="chicken-{{ $index }}-breast">Pecho</label>
                                        </div>
                                        <div class="pos-radio-option">
                                            <input
                                                type="radio"
                                                wire:model.live="cartItems.{{ $index }}.chicken_cut_type"
                                                value="PIERNA"
                                                id="chicken-{{ $index }}-leg"
                                            >
                                            <label for="chicken-{{ $index }}-leg">Pierna</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- CONTROLES DE CANTIDAD MEJORADOS --}}
                            <div class="pos-quantity-controls {{ $item['quantity'] <= 1 ? 'at-minimum' : '' }}">
                                {{-- Botón Disminuir --}}
                                <button
                                    wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                    class="pos-quantity-btn decrease"
                                    {{ (!$canClearCart || $item['quantity'] <= 1) ? 'disabled' : '' }}
                                    title="Disminuir cantidad"
                                    x-data="{ pressed: false }"
                                    @click="pressed = true; setTimeout(() => pressed = false, 150)"
                                    :class="{ 'scale-95': pressed }"
                                >
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
                                    </svg>
                                </button>

                                {{-- Valor de Cantidad con Animación --}}
                                <div
                                    class="pos-quantity-value"
                                    x-data="{ updating: false, currentValue: {{ $item['quantity'] }} }"
                                    x-init="$watch('currentValue', () => { updating = true; setTimeout(() => updating = false, 200) })"
                                    :class="{ 'updating': updating }"
                                    wire:key="quantity-{{ $index }}-{{ $item['quantity'] }}"
                                >
                                    {{ $item['quantity'] }}
                                </div>

                                {{-- Botón Aumentar --}}
                                <button
                                    wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                    class="pos-quantity-btn increase"
                                    {{ !$canClearCart ? 'disabled' : '' }}
                                    title="Aumentar cantidad"
                                    x-data="{ pressed: false }"
                                    @click="pressed = true; setTimeout(() => pressed = false, 150)"
                                    :class="{ 'scale-95': pressed }"
                                >
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>

                                {{-- Total del Item Mejorado --}}
                                <div class="pos-quantity-total">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    {{ number_format($item['quantity'] * $item['unit_price'], 2) }}
                                </div>
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
                        {{-- HEADER CON INFORMACIÓN CONTEXTUAL --}}
                        <div class="pos-totals-header">
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
                            <div class="pos-total-row">
                                <span>Subtotal:</span>
                                <span class="pos-total-amount">S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="pos-total-row">
                                <span>IGV (18%):</span>
                                <span class="pos-total-amount">S/ {{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="pos-total-row final">
                                <span>
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 6px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Total:
                                </span>
                                <span class="pos-total-amount">S/ {{ number_format($total, 2) }}</span>
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
                                    wire:click="mountAction('processBilling')"
                                    class="pos-action-btn success"
                                    {{ (!count($cartItems) || !$this->hasOpenCashRegister) ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-credit-card style="width: 20px; height: 20px;" />
                                    Emitir Comprobante
                                </button>
                            @endif
                        @elseif(!$order || ($order && !$order->invoices()->exists()))
                            {{-- Oculto por requerimiento: siempre se imprime (no eliminar funcionalidad) --}}
                            <button
                                wire:click="processOrder"
                                class="pos-action-btn primary"
                                style="display:none;"
                                {{ (!count($cartItems) || !$this->hasOpenCashRegister) ? 'disabled' : '' }}
                            >
                                <x-heroicon-m-check-circle style="width: 20px; height: 20px;" />
                                Guardar Orden
                            </button>
                        @endif

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
                    $wire.on('open-print-window', (event) => {
                        if (this.printProcessing) return;
                        this.printProcessing = true;

                        console.log('🖨️ POS Interface - Imprimiendo comprobante...', event);

                        let invoiceId = Array.isArray(event) ? (event[0]?.id || event[0]) : (event?.id || event);

                        if (!invoiceId) {
                            console.error('❌ Error: ID de comprobante no encontrado');
                            this.printProcessing = false;
                            return;
                        }

                        setTimeout(() => {
                            const printUrl = `/print/invoice/${invoiceId}`;
                            console.log('🔗 Abriendo ventana de impresión:', printUrl);
                            window.open(printUrl, 'invoice_print_' + invoiceId, 'width=800,height=600,scrollbars=yes,resizable=yes');
                            this.printProcessing = false;
                        }, 800);
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
