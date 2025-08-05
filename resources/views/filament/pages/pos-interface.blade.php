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
            font-weight: 500;
            font-size: 11px;
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
        }
        
        .pos-search-input {
            width: 100%;
            padding: clamp(6px, 1.5vw, 12px);
            border: 1px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            font-size: clamp(12px, 2vw, 16px);
            transition: var(--pos-transition);
            background: var(--pos-gray-50);
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
            font-size: 11px;
            font-weight: 600;
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
            padding: 4px 2px;
            border: 1px solid var(--pos-gray-200);
            border-radius: 4px;
            background: white;
            color: #6b7280;
            font-size: 0;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            position: relative;
            min-height: 24px;
            height: 24px;
        }
        
        .pos-quick-action-btn:hover:not(:disabled) {
            background: var(--pos-gray-50);
            border-color: var(--pos-primary);
            color: var(--pos-primary);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .pos-quick-action-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        /* COLORES REPRESENTATIVOS PARA CADA BOTÓN */
        .pos-quick-action-btn.btn-mapa {
            background: #1e40af;
            border-color: #1e40af;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-comanda {
            background: #c2410c;
            border-color: #c2410c;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-precuenta {
            background: #d97706;
            border-color: #d97706;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-reabrir {
            background: #15803d;
            border-color: #15803d;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-dividir {
            background: #7c3aed;
            border-color: #7c3aed;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-transferir {
            background: #4338ca;
            border-color: #4338ca;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-liberar {
            background: #475569;
            border-color: #475569;
            color: #ffffff;
        }
        
        .pos-quick-action-btn.btn-cancelar {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }
        
        .pos-quick-action-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .pos-quick-action-icon {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
        }
        
        .pos-quick-action-btn span {
            display: none;
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
        }
        
        .pos-cart-item-name {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
            flex: 1;
            margin-right: 8px;
        }
        
        .pos-cart-item-price {
            font-size: 11px;
            color: #6b7280;
            white-space: nowrap;
        }
        
        /* CONTROLES DE CANTIDAD MEJORADOS */
        .pos-quantity-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 6px;
            border-radius: 6px;
            border: 1px solid var(--pos-gray-200);
        }
        
        .pos-quantity-btn {
            width: 28px;
            height: 28px;
            border: none;
            border-radius: 4px;
            background: var(--pos-gray-100);
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .pos-quantity-btn:hover:not(:disabled) {
            background: var(--pos-primary);
            color: white;
        }
        
        .pos-quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pos-quantity-value {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            min-width: 40px;
            text-align: center;
        }
        
        .pos-quantity-total {
            font-size: 14px;
            font-weight: 700;
            color: var(--pos-success);
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
        
        /* TOTALES DEL CARRITO OPTIMIZADOS PARA 1024x637 */
        .pos-cart-totals {
            padding: 6px;
            background: white;
            border: 1px solid var(--pos-border-subtle);
            border-radius: var(--pos-border-radius);
            margin: 6px;
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
        }
        
        .pos-totals-container {
            background: var(--pos-gray-50);
            padding: 4px 6px;
            border-radius: 6px;
            margin-bottom: 6px;
        }
        
        .pos-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .pos-total-row:last-child {
            margin-bottom: 0;
            padding-top: 3px;
            border-top: 1px solid var(--pos-gray-200);
            font-weight: 700;
            font-size: 12px;
        }
        
        .pos-total-row.final {
            color: var(--pos-success);
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
                font-size: 12px;
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
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
                font-size: 11px;
                white-space: nowrap;
            }

            .pos-cart {
                max-height: 200px;
            }

            .pos-quick-actions-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2px;
            }

            .pos-quick-action-btn {
                padding: 6px 2px;
                font-size: 10px;
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
                font-size: 10px;
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

        // INICIALIZAR CATEGORÍAS COLAPSADAS AL CARGAR
        document.addEventListener('DOMContentLoaded', function() {
            const categoriesSection = document.getElementById('pos-categories');
            const mainContainer = document.querySelector('.pos-main-container');

            if (categoriesSection && mainContainer) {
                // Inicializar ambos elementos colapsados
                categoriesSection.classList.add('collapsed');
                mainContainer.classList.add('categories-collapsed');
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
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar productos..."
                        class="pos-search-input"
                    />
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
                    <div class="pos-cart-title" style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="background: var(--pos-success); color: white; padding: 4px 12px; border-radius: 16px; font-size: 14px; font-weight: 600;">
                            {{ count($cartItems) }} items
                        </span>
                        
                        {{-- CONTROLES MOVIDOS AL LADO DERECHO DEL "0 items" --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
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
                            {{-- Mapa --}}
                            <button
                                wire:click="mountAction('backToTableMap')"
                                class="pos-quick-action-btn btn-mapa"
                                title="Mapa"
                            >
                                <x-heroicon-o-map class="pos-quick-action-icon" />
                            </button>
                            
                            {{-- Comanda --}}
                            <button 
                                wire:click="mountAction('printComanda')"
                                class="pos-quick-action-btn btn-comanda"
                                {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }}
                                title="Comanda"
                            >
                                <x-heroicon-o-document-text class="pos-quick-action-icon" />
                            </button>
                            
                            {{-- Pre-Cuenta --}}
                            <button 
                                wire:click="mountAction('printPreBillNew')"
                                class="pos-quick-action-btn btn-precuenta"
                                {{ !($this->order || !empty($this->cartItems)) ? 'disabled' : '' }}
                                title="Pre-Cuenta"
                            >
                                <x-heroicon-o-document-duplicate class="pos-quick-action-icon" />
                            </button>
                            
                            {{-- Reabrir --}}
                            <button 
                                wire:click="mountAction('reopen_order_for_editing')"
                                class="pos-quick-action-btn btn-reabrir"
                                {{ !($this->order instanceof \App\Models\Order && !$this->order->invoices()->exists()) ? 'disabled' : '' }}
                                title="Reabrir"
                            >
                                <x-heroicon-o-lock-open class="pos-quick-action-icon" />
                            </button>
                            
                            {{-- Dividir --}}
                            <button 
                                wire:click="mountAction('split_items')"
                                class="pos-quick-action-btn btn-dividir"
                                {{ !($this->order !== null && count($this->order->orderDetails ?? []) > 0) ? 'disabled' : '' }}
                                title="Dividir"
                            >
                                <x-heroicon-o-scissors class="pos-quick-action-icon" />
                            </button>
                            
                            {{-- Transferir --}}
                            @if(!auth()->user()->hasRole(['waiter', 'cashier']))
                                <button 
                                    wire:click="mountAction('transferOrder')"
                                    class="pos-quick-action-btn btn-transferir"
                                    {{ !($this->order && $this->order->table_id && $this->order->status === 'open') ? 'disabled' : '' }}
                                    title="Transferir"
                                >
                                    <x-heroicon-o-arrow-path-rounded-square class="pos-quick-action-icon" />
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
                                <div class="pos-cart-item-name">{{ $item['name'] }}</div>
                                <div class="pos-cart-item-price">S/ {{ number_format($item['unit_price'], 2) }} c/u</div>
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

                            {{-- CONTROLES DE CANTIDAD --}}
                            <div class="pos-quantity-controls">
                                <button
                                    wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                    class="pos-quantity-btn"
                                    {{ !$canClearCart ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-minus style="width: 16px; height: 16px;" />
                                </button>
                                
                                <div class="pos-quantity-value">{{ $item['quantity'] }}</div>
                                
                                <button
                                    wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                    class="pos-quantity-btn"
                                    {{ !$canClearCart ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-plus style="width: 16px; height: 16px;" />
                                </button>
                                
                                <div class="pos-quantity-total">
                                    S/ {{ number_format($item['quantity'] * $item['unit_price'], 2) }}
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
                        <div class="pos-totals-container">
                            <div class="pos-total-row">
                                <span>Subtotal:</span>
                                <span>S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="pos-total-row">
                                <span>IGV (18%):</span>
                                <span>S/ {{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="pos-total-row final">
                                <span>Total:</span>
                                <span>S/ {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        {{-- BOTONES DE ACCIÓN --}}
                        @if($selectedTableId === null && !$order)
                            @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                <button
                                    wire:click="mountAction('processBilling')"
                                    class="pos-action-btn success"
                                    {{ !count($cartItems) ? 'disabled' : '' }}
                                >
                                    <x-heroicon-m-credit-card style="width: 20px; height: 20px;" />
                                    Emitir Comprobante
                                </button>
                            @endif
                        @elseif(!$order || ($order && !$order->invoices()->exists()))
                            <button
                                wire:click="processOrder"
                                class="pos-action-btn primary"
                                {{ !count($cartItems) ? 'disabled' : '' }}
                            >
                                <x-heroicon-m-check-circle style="width: 20px; height: 20px;" />
                                Guardar Orden
                            </button>
                        @endif

                        @if($order && !$order->invoices()->exists() && auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                            <button
                                wire:click="mountAction('processBilling')"
                                class="pos-action-btn success"
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