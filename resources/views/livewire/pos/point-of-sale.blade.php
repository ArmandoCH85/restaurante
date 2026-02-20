<div class="font-sans bg-gray-100 pos-container dark:bg-gray-900">
    <!-- Incluir Leaflet CSS y JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Incluir SweetAlert2 directamente en esta vista -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Utilidades para truncar texto con múltiples líneas */
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Mejorar las tarjetas de productos */
        .product-card {
            min-height: 280px;
            display: flex;
            flex-direction: column;
        }

        .product-card h3 {
            word-wrap: break-word;
            hyphens: auto;
            line-height: 1.3;
        }

        /* ===== SISTEMA POS OPTIMIZADO - PALETA PROFESIONAL ===== */
        :root {
            /* Colores principales optimizados para POS */
            --color-primary: #3C50E0;
            --color-primary-light: #E8ECFF;
            --color-primary-dark: #2A3BB7;

            /* Estados de alta visibilidad para POS */
            --color-success: #22C55E;
            --color-success-light: #DCFCE7;
            --color-success-dark: #15803D;

            --color-warning: #F59E0B;
            --color-warning-light: #FEF3C7;
            --color-warning-dark: #D97706;

            --color-danger: #EF4444;
            --color-danger-light: #FEE2E2;
            --color-danger-dark: #DC2626;

            --color-neutral: #6B7280;
            --color-neutral-light: #F3F4F6;
            --color-neutral-dark: #374151;

            /* Superficies optimizadas para POS */
            --color-surface: #F2F7FF;
            --color-surface-elevated: #FFFFFF;
            --color-surface-hover: #E8ECFF;

            /* Escala de grises mejorada */
            --color-gray-50: #f9fafb;
            --color-gray-100: #f3f4f6;
            --color-gray-200: #e5e7eb;
            --color-gray-300: #d1d5db;
            --color-gray-400: #9ca3af;
            --color-gray-500: #6b7280;
            --color-gray-600: #4b5563;
            --color-gray-700: #374151;
            --color-gray-800: #1f2937;
            --color-gray-900: #111827;

            /* Espaciado en grid de 4px para POS */
            --spacing-xs: 0.25rem;   /* 4px */
            --spacing-sm: 0.5rem;    /* 8px */
            --spacing-md: 1rem;      /* 16px */
            --spacing-lg: 1.5rem;    /* 24px */
            --spacing-xl: 2rem;      /* 32px */
            --spacing-2xl: 3rem;     /* 48px */

            /* Bordes con radio estándar de 8px */
            --border-radius-sm: 0.25rem;  /* 4px */
            --border-radius-md: 0.5rem;   /* 8px */
            --border-radius-lg: 0.75rem;  /* 12px */
            --border-radius-xl: 1rem;     /* 16px */
            --border-radius-full: 9999px;

            /* Sombras optimizadas para POS */
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.06);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.16), 0 10px 10px -5px rgba(0, 0, 0, 0.08);

            /* Transiciones optimizadas - 150ms estándar */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);

            /* Tipografía - Inter font system */
            --font-family-base: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            --font-size-xs: 0.75rem;    /* 12px */
            --font-size-sm: 0.875rem;   /* 14px */
            --font-size-base: 1rem;     /* 16px */
            --font-size-lg: 1.125rem;   /* 18px */
            --font-size-xl: 1.25rem;    /* 20px */
            --font-size-2xl: 1.5rem;    /* 24px */

            /* Z-index layers */
            --z-dropdown: 10;
            --z-sticky: 20;
            --z-fixed: 30;
            --z-modal-backdrop: 40;
            --z-modal: 50;
            --z-popover: 60;
            --z-tooltip: 70;
            --z-toast: 80;

            /* Compatibilidad con variables anteriores */
            --primary: var(--color-primary);
            --secondary: var(--color-primary-light);
            --background: var(--color-surface);
            --sidebar: var(--color-gray-800);
            --success: var(--color-success);
            --warning: var(--color-warning);
            --danger: var(--color-danger);
            --text-primary: var(--color-gray-900);
            --text-secondary: var(--color-gray-600);
            --border: var(--color-gray-200);
            --radius: var(--border-radius-md);
            --transition: var(--transition-fast);
        }

        /* ===== LAYOUT OPTIMIZADO PARA POS ===== */
        .pos-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background: var(--color-surface);
            font-family: var(--font-family-base);
        }

        .pos-main {
            display: flex;
            flex: 1;
            overflow: hidden;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm);
        }

        /* Panel de categorías optimizado para POS */
        .pos-categories {
            width: 300px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border: 2px solid var(--color-primary-light);
            border-radius: var(--border-radius-lg);
            overflow-y: auto;
            transition: all var(--transition-fast);
            box-shadow: var(--shadow-md);
            scrollbar-width: thin;
            scrollbar-color: var(--color-primary-light) transparent;
        }

        .pos-categories::-webkit-scrollbar {
            width: 6px;
        }

        .pos-categories::-webkit-scrollbar-track {
            background: transparent;
        }

        .pos-categories::-webkit-scrollbar-thumb {
            background: var(--color-primary-light);
            border-radius: var(--border-radius-full);
        }

        /* Panel de productos optimizado */
        .pos-products {
            flex: 1;
            background: var(--color-surface);
            overflow-y: auto;
            min-width: 0;
            border-radius: var(--border-radius-lg);
            scrollbar-width: thin;
            scrollbar-color: var(--color-primary-light) transparent;
        }

        .pos-products::-webkit-scrollbar {
            width: 8px;
        }

        .pos-products::-webkit-scrollbar-track {
            background: var(--color-surface-hover);
            border-radius: var(--border-radius-full);
        }

        .pos-products::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: var(--border-radius-full);
        }

        /* Panel de carrito optimizado para POS */
        .pos-cart {
            width: 400px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border: 2px solid var(--color-primary-light);
            border-radius: var(--border-radius-lg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        /* ===== DISEÑO RESPONSIVO OPTIMIZADO PARA POS ===== */

        /* Tablets grandes (1024px - 1279px) */
        @media (max-width: 1279px) and (min-width: 1024px) {
            .pos-categories {
                width: 280px;
            }

            .pos-cart {
                width: 380px;
            }

            .pos-products .grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: var(--spacing-md);
            }
        }

        /* Tablets (768px - 1023px) */
        @media (max-width: 1023px) and (min-width: 768px) {
            .pos-main {
                gap: var(--spacing-xs);
                padding: var(--spacing-xs);
            }

            .pos-categories {
                width: 260px;
                border-radius: var(--border-radius-md);
            }

            .pos-cart {
                width: 340px;
                border-radius: var(--border-radius-md);
            }

            .pos-products .grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: var(--spacing-sm);
                padding: var(--spacing-md);
            }

            .product-card {
                min-height: 200px;
            }
        }

        /* Móviles landscape (481px - 767px) */
        @media (max-width: 767px) and (min-width: 481px) {
            .pos-main {
                flex-direction: column;
                gap: var(--spacing-xs);
                padding: var(--spacing-xs);
            }

            .pos-categories {
                width: 100%;
                height: 140px;
                border: 2px solid var(--color-primary-light);
                border-bottom: 2px solid var(--color-primary);
                border-radius: var(--border-radius-md);
                overflow-x: auto;
                overflow-y: hidden;
                order: 1;
            }

            .pos-categories nav {
                display: flex;
                gap: var(--spacing-sm);
                padding: var(--spacing-md);
                min-width: max-content;
            }

            .pos-categories button {
                white-space: nowrap;
                min-width: 140px;
                min-height: 48px;
                border-radius: var(--border-radius-md);
            }

            .pos-products {
                flex: 1;
                order: 2;
                border-radius: var(--border-radius-md);
            }

            .pos-cart {
                width: 100%;
                height: 220px;
                border: 2px solid var(--color-primary-light);
                border-top: 2px solid var(--color-primary);
                border-radius: var(--border-radius-md);
                order: 3;
            }

            .pos-products .grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: var(--spacing-sm);
                padding: var(--spacing-md);
            }

            .product-card {
                min-height: 180px;
            }
        }

        /* Móviles portrait (hasta 480px) */
        @media (max-width: 480px) {
            .pos-main {
                flex-direction: column;
                gap: var(--spacing-xs);
                padding: var(--spacing-xs);
            }

            .pos-categories {
                width: 100%;
                height: 120px;
                border-radius: var(--border-radius-sm);
            }

            .pos-categories nav {
                gap: var(--spacing-xs);
                padding: var(--spacing-sm);
            }

            .pos-categories button {
                min-width: 120px;
                min-height: 44px;
                font-size: var(--font-size-xs);
                padding: var(--spacing-sm);
            }

            .pos-cart {
                height: 200px;
                border-radius: var(--border-radius-sm);
            }

            .pos-products .grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: var(--spacing-xs);
                padding: var(--spacing-sm);
            }

            .product-card {
                min-height: 160px;
            }

            .product-card .h-36 {
                height: 80px !important;
            }
        }

        /* ===== ELEMENTOS DEL CARRITO OPTIMIZADOS PARA POS ===== */
        .cart-standard {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            background: var(--color-surface-hover);
            scrollbar-width: thin;
            scrollbar-color: var(--color-primary-light) transparent;
        }

        .cart-standard::-webkit-scrollbar {
            width: 6px;
        }

        .cart-standard::-webkit-scrollbar-track {
            background: transparent;
        }

        .cart-standard::-webkit-scrollbar-thumb {
            background: var(--color-primary-light);
            border-radius: var(--border-radius-full);
        }

        .cart-item-standard {
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            margin: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-lg);
            border: 2px solid var(--color-primary-light);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-fast);
            position: relative;
            overflow: hidden;
        }

        .cart-item-standard::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-success), var(--color-success-light));
            border-radius: var(--border-radius-full);
        }

        .cart-item-standard:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px) scale(1.01);
            border-color: var(--color-primary);
        }

        .cart-item-standard-content {
            padding: var(--spacing-md);
        }

        .cart-item-standard-name {
            font-weight: 700;
            color: var(--color-gray-900);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-sm);
            line-height: 1.4;
            letter-spacing: -0.025em;
        }

        .cart-item-standard-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: var(--font-size-xs);
            color: var(--color-gray-600);
            margin-bottom: var(--spacing-sm);
            background: var(--color-surface-hover);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-md);
        }

        .cart-item-standard-price span {
            font-weight: 700;
            color: var(--color-primary);
            font-size: var(--font-size-sm);
        }

        .edit-price-btn {
            padding: var(--spacing-xs);
            color: var(--color-gray-500);
            border-radius: var(--border-radius-md);
            transition: all var(--transition-fast);
            background: transparent;
            border: 1px solid transparent;
            min-height: 32px;
            min-width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .edit-price-btn:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
            border-color: var(--color-primary);
            transform: scale(1.05);
        }

        .cart-item-standard-quantity {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
            margin: var(--spacing-md) 0;
            background: var(--color-surface-elevated);
            padding: var(--spacing-sm);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--color-primary-light);
        }

        .quantity-btn-minus,
        .quantity-btn-plus {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-lg);
            border: 2px solid var(--color-primary-light);
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            color: var(--color-primary);
            font-weight: 700;
            font-size: var(--font-size-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .quantity-btn-minus::before,
        .quantity-btn-plus::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left var(--transition-normal);
        }

        .quantity-btn-minus:hover::before,
        .quantity-btn-plus:hover::before {
            left: 100%;
        }

        .quantity-btn-minus:hover,
        .quantity-btn-plus:hover {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            border-color: var(--color-primary);
            transform: scale(1.08);
            box-shadow: var(--shadow-lg);
        }

        .quantity-btn-minus:active,
        .quantity-btn-plus:active {
            transform: scale(1.02);
        }

        .quantity-value {
            font-weight: 800;
            font-size: var(--font-size-lg);
            color: var(--color-primary);
            min-width: 48px;
            text-align: center;
            background: var(--color-primary-light);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-md);
        }

        .cart-item-standard-subtotal {
            text-align: right;
            font-weight: 800;
            font-size: var(--font-size-lg);
            color: var(--color-success);
            margin-bottom: var(--spacing-sm);
            background: var(--color-success-light);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-md);
            border-left: 4px solid var(--color-success);
        }

        .cart-item-standard-remove {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            width: 100%;
            padding: var(--spacing-sm);
            background: linear-gradient(135deg, var(--color-danger-light) 0%, var(--color-surface-elevated) 100%);
            color: var(--color-danger);
            border: 2px solid var(--color-danger-light);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-xs);
            font-weight: 600;
            transition: all var(--transition-fast);
            cursor: pointer;
            min-height: 40px;
            position: relative;
            overflow: hidden;
        }

        .cart-item-standard-remove::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left var(--transition-normal);
        }

        .cart-item-standard-remove:hover::before {
            left: 100%;
        }

        .cart-item-standard-remove:hover {
            background: linear-gradient(135deg, var(--color-danger) 0%, var(--color-danger-dark) 100%);
            color: white;
            border-color: var(--color-danger);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .cart-item-standard-note {
            margin-top: var(--spacing-sm);
            padding: var(--spacing-sm);
            background: var(--color-warning-light);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-xs);
            color: var(--color-warning-dark);
            border-left: 4px solid var(--color-warning);
            font-style: italic;
            position: relative;
        }

        .cart-item-standard-note::before {
            content: '💬';
            position: absolute;
            top: var(--spacing-xs);
            left: var(--spacing-xs);
            font-size: var(--font-size-sm);
        }

        .cart-item-standard-note span {
            font-weight: 700;
            margin-left: var(--spacing-lg);
        }

        .cart-empty-standard {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-2xl) var(--spacing-lg);
            text-align: center;
            color: var(--color-gray-500);
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border: 2px dashed var(--color-primary-light);
            border-radius: var(--border-radius-lg);
            margin: var(--spacing-md);
        }

        .cart-empty-icon-standard {
            width: 64px;
            height: 64px;
            margin-bottom: var(--spacing-lg);
            opacity: 0.6;
            color: var(--color-primary);
            background: var(--color-primary-light);
            border-radius: var(--border-radius-full);
            padding: var(--spacing-md);
            box-shadow: var(--shadow-md);
        }

        /* ===== RESUMEN DE PEDIDO OPTIMIZADO PARA POS ===== */
        .order-summary-standard {
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-top: 3px solid var(--color-primary);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .order-summary-standard::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-primary-light), var(--color-primary));
        }

        .order-summary-standard-title {
            display: flex;
            align-items: center;
            font-weight: 800;
            font-size: var(--font-size-xs);
            color: var(--color-primary);
            margin-bottom: var(--spacing-md);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: var(--color-primary-light);
            padding: var(--spacing-sm);
            border-radius: var(--border-radius-md);
        }

        .order-summary-standard-title svg {
            margin-right: var(--spacing-sm);
            color: var(--color-primary);
        }

        .order-summary-standard-content {
            background: var(--color-surface-elevated);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-md);
            border: 1px solid var(--color-primary-light);
            box-shadow: var(--shadow-sm);
        }

        .order-summary-standard-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--font-size-sm);
            color: var(--color-gray-600);
            margin-bottom: var(--spacing-sm);
            padding: var(--spacing-xs) var(--spacing-sm);
            background: var(--color-surface-hover);
            border-radius: var(--border-radius-sm);
        }

        .order-summary-standard-row span:last-child {
            font-weight: 700;
            color: var(--color-gray-900);
        }

        .order-summary-standard-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            font-size: var(--font-size-lg);
            color: var(--color-primary);
            padding: var(--spacing-md);
            border-top: 2px solid var(--color-primary-light);
            margin-top: var(--spacing-sm);
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-surface-elevated) 100%);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
        }

        .order-note-standard {
            margin-top: var(--spacing-lg);
        }

        .order-note-standard-label {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: var(--font-size-xs);
            color: var(--color-primary);
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: var(--color-primary-light);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-md);
        }

        .order-note-standard-label svg {
            margin-right: var(--spacing-sm);
        }

        .order-note-standard-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--color-primary-light);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-900);
            background: var(--color-surface-elevated);
            transition: all var(--transition-fast);
            resize: vertical;
            min-height: 80px;
            font-family: var(--font-family-base);
        }

        .order-note-standard-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(60, 80, 224, 0.15);
            background: white;
        }

        .order-note-standard-input::placeholder {
            color: var(--color-gray-400);
            font-style: italic;
        }

        /* ===== ESTILOS PARA MESAS ===== */
        .table-visual {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid;
            transition: all var(--transition) ease;
            font-weight: 600;
        }

        .table-square {
            border-radius: var(--radius);
        }

        .table-round {
            border-radius: 50%;
        }

        #modal-transferir-mesa .table-visual {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        /* ===== SCROLLBARS PERSONALIZADOS ===== */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-track {
            background: #2d3748;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #4a5568;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }

        /* ===== OPTIMIZACIONES TÁCTILES MEJORADAS ===== */
        @media (hover: none) and (pointer: coarse) {
            .quantity-btn-minus,
            .quantity-btn-plus {
                width: 48px;
                height: 48px;
                font-size: var(--font-size-xl);
            }

            .cart-item-standard-remove {
                padding: var(--spacing-md);
                font-size: var(--font-size-sm);
                min-height: 48px;
            }

            .edit-price-btn {
                min-height: 48px;
                min-width: 48px;
            }

            button, .cursor-pointer {
                min-height: 48px;
                min-width: 48px;
            }

            /* Desactivar hover en dispositivos táctiles */
            .cart-item-standard:hover,
            .quantity-btn-minus:hover,
            .quantity-btn-plus:hover,
            .cart-item-standard-remove:hover,
            .edit-price-btn:hover {
                transform: none;
                box-shadow: var(--shadow-md);
            }

            /* Efectos de press para feedback táctil */
            .cart-item-standard:active,
            .quantity-btn-minus:active,
            .quantity-btn-plus:active,
            .cart-item-standard-remove:active,
            .edit-price-btn:active {
                transform: scale(0.98);
                transition: transform 0.1s ease;
            }
        }

        /* ===== ANIMACIONES OPTIMIZADAS PARA POS ===== */
        .fade-in {
            animation: fadeIn var(--transition-normal) ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(var(--spacing-sm));
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scale-hover:hover {
            transform: scale(1.02);
            transition: transform var(--transition-fast);
        }

        /* Animación de shimmer para elementos de carga */
        .shimmer {
            background: linear-gradient(90deg,
                var(--color-surface-elevated) 25%,
                var(--color-surface-hover) 50%,
                var(--color-surface-elevated) 75%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Animación de pulso para elementos importantes */
        .pulse-primary {
            animation: pulsePrimary 2s infinite;
        }

        @keyframes pulsePrimary {
            0% {
                box-shadow: var(--shadow-md), 0 0 0 0 rgba(60, 80, 224, 0.4);
            }
            70% {
                box-shadow: var(--shadow-md), 0 0 0 8px rgba(60, 80, 224, 0);
            }
            100% {
                box-shadow: var(--shadow-md), 0 0 0 0 rgba(60, 80, 224, 0);
            }
        }

        /* ===== MEJORAS DE ACCESIBILIDAD ===== */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus visible mejorado */
        .focus-visible:focus-visible {
            outline: 3px solid var(--color-primary);
            outline-offset: 2px;
            border-radius: var(--border-radius-md);
        }

        /* ===== MODO OSCURO OPTIMIZADO ===== */
        .dark {
            --color-surface: #111827;
            --color-surface-elevated: #1F2937;
            --color-surface-hover: #374151;
            --color-primary-light: #4C63D2;
            --color-gray-900: #F9FAFB;
            --color-gray-600: #D1D5DB;
            --color-gray-500: #9CA3AF;
        }

        .dark .pos-container {
            background: var(--color-surface);
        }

        .dark .pos-categories,
        .dark .pos-cart {
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
        }

        .dark .cart-standard {
            background: var(--color-surface-hover);
        }

        .dark .cart-item-standard {
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
        }

        .dark .cart-item-standard-name {
            color: var(--color-gray-900);
        }

        .dark .cart-item-standard-price span {
            color: var(--color-primary-light);
        }

        .dark .quantity-btn-minus,
        .dark .quantity-btn-plus {
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
            color: var(--color-primary-light);
        }

        .dark .quantity-btn-minus:hover,
        .dark .quantity-btn-plus:hover {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary) 100%);
            color: white;
        }

        .dark .quantity-value {
            background: var(--color-primary-light);
            color: white;
        }

        .dark .cart-item-standard-subtotal {
            background: var(--color-success-dark);
            color: var(--color-success-light);
        }

        .dark .cart-item-standard-remove {
            background: linear-gradient(135deg, var(--color-danger-dark) 0%, var(--color-surface-elevated) 100%);
            border-color: var(--color-danger-dark);
        }

        .dark .cart-item-standard-remove:hover {
            background: linear-gradient(135deg, var(--color-danger) 0%, var(--color-danger-dark) 100%);
        }

        .dark .cart-empty-standard {
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
            color: var(--color-gray-500);
        }

        .dark .cart-empty-icon-standard {
            background: var(--color-primary-light);
            color: white;
        }

        .dark .order-summary-standard {
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
        }

        .dark .order-summary-standard-title {
            background: var(--color-primary-light);
            color: white;
        }

        .dark .order-summary-standard-content {
            background: var(--color-surface-elevated);
            border-color: var(--color-primary-light);
        }

        .dark .order-summary-standard-row {
            background: var(--color-surface-hover);
            color: var(--color-gray-600);
        }

        .dark .order-summary-standard-row span:last-child {
            color: var(--color-gray-900);
        }

        .dark .order-summary-standard-total {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-surface-elevated) 100%);
            color: white;
        }

        .dark .order-note-standard-label {
            background: var(--color-primary-light);
            color: white;
        }

        .dark .order-note-standard-input {
            background: var(--color-surface-elevated);
            border-color: var(--color-primary-light);
            color: var(--color-gray-900);
        }

        .dark .order-note-standard-input:focus {
            background: var(--color-surface-hover);
            border-color: var(--color-primary-light);
        }

        /* ===== ESTILOS COMPACTOS PARA CARRITO OPTIMIZADO ===== */

        /* Contenedor de item compacto */
        .cart-item-compact {
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            margin: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-md);
            border: 1px solid var(--color-primary-light);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-fast);
            position: relative;
            overflow: hidden;
            padding: var(--spacing-xs);
        }

        .cart-item-compact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--color-success), var(--color-success-light));
            border-radius: var(--border-radius-full);
        }

        .cart-item-compact:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
            border-color: var(--color-primary);
        }

        /* Fila principal compacta */
        .cart-item-main-row {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: var(--spacing-xs);
            align-items: center;
            margin-bottom: var(--spacing-xs);
        }

        /* Fila secundaria compacta */
        .cart-item-secondary-row {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding-top: var(--spacing-xs);
            border-top: 1px solid var(--color-border-light);
        }

        /* Nombre del producto compacto */
        .cart-item-name-compact {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--color-text-primary);
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Precio compacto */
        .cart-item-price-compact {
            font-size: 0.7rem;
            color: var(--color-text-secondary);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .cart-item-price-compact span {
            font-weight: 600;
            color: var(--color-primary);
        }

        /* Botón editar precio compacto */
        .edit-price-btn-compact {
            color: var(--color-primary);
            background-color: var(--color-primary-light);
            padding: 0.125rem;
            border-radius: var(--border-radius-full);
            transition: all var(--transition-fast);
        }

        .edit-price-btn-compact:hover {
            background-color: var(--color-primary);
            color: white;
        }

        /* Controles de cantidad compactos */
        .cart-item-quantity-compact {
            display: flex;
            align-items: center;
            background-color: var(--color-surface-hover);
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            border: 1px solid var(--color-border-light);
        }

        .quantity-btn-compact {
            background-color: var(--color-primary-light);
            color: var(--color-primary);
            border: none;
            padding: 0.25rem 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all var(--transition-fast);
            cursor: pointer;
            min-width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn-compact:hover {
            background-color: var(--color-primary);
            color: white;
        }

        .quantity-value-compact {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-text-primary);
            background-color: var(--color-surface-elevated);
            min-width: 32px;
            text-align: center;
            line-height: 1;
        }

        /* Subtotal compacto */
        .cart-item-subtotal-compact {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--color-success);
            background-color: var(--color-success-light);
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            text-align: center;
            min-width: 60px;
        }

        /* Botón eliminar compacto */
        .cart-item-remove-compact {
            background-color: var(--color-danger-light);
            color: var(--color-danger);
            border: none;
            padding: 0.25rem;
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-fast);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }

        .cart-item-remove-compact:hover {
            background-color: var(--color-danger);
            color: white;
            transform: scale(1.05);
        }

        /* Notas compactas */
        .cart-item-note-compact {
            background-color: var(--color-warning-light);
            border-left: 2px solid var(--color-warning);
            padding: 0.25rem 0.375rem;
            margin-top: var(--spacing-xs);
            font-size: 0.7rem;
            font-style: italic;
            color: var(--color-warning-dark);
            border-radius: var(--border-radius-sm);
            line-height: 1.2;
        }

        .cart-item-note-compact span {
            font-weight: 600;
        }

        /* ===== MODO OSCURO PARA ESTILOS COMPACTOS ===== */
        .dark .cart-item-compact {
            background: linear-gradient(135deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
        }

        .dark .cart-item-name-compact {
            color: var(--color-gray-900);
        }

        .dark .cart-item-price-compact {
            color: var(--color-gray-600);
        }

        .dark .cart-item-price-compact span {
            color: var(--color-primary-light);
        }

        .dark .edit-price-btn-compact {
            background-color: var(--color-primary-light);
            color: white;
        }

        .dark .edit-price-btn-compact:hover {
            background-color: var(--color-primary);
            color: white;
        }

        .dark .cart-item-quantity-compact {
            background-color: var(--color-surface-hover);
            border-color: var(--color-primary-light);
        }

        .dark .quantity-btn-compact {
            background-color: var(--color-primary-light);
            color: white;
        }

        .dark .quantity-btn-compact:hover {
            background-color: var(--color-primary);
            color: white;
        }

        .dark .quantity-value-compact {
            background-color: var(--color-surface-elevated);
            color: var(--color-gray-900);
        }

        .dark .cart-item-subtotal-compact {
            background-color: var(--color-success-dark);
            color: var(--color-success-light);
        }

        .dark .cart-item-remove-compact {
            background-color: var(--color-danger-dark);
            color: var(--color-danger-light);
        }

        .dark .cart-item-remove-compact:hover {
            background-color: var(--color-danger);
            color: white;
        }

        .dark .cart-item-note-compact {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--color-warning-light);
            border-color: var(--color-warning);
        }

        /* ===== ESTILOS COMPACTOS PARA RESUMEN DEL PEDIDO ===== */

        /* Contenedor del resumen compacto */
        .order-summary-compact {
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-top: 2px solid var(--color-primary);
            padding: var(--spacing-sm);
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .order-summary-compact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-primary-light), var(--color-primary));
        }

        /* Título del resumen compacto */
        .order-summary-compact-title {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 0.7rem;
            color: var(--color-primary);
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--color-primary-light);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
        }

        .order-summary-compact-title svg {
            margin-right: var(--spacing-xs);
            color: var(--color-primary);
        }

        /* Contenido del resumen compacto */
        .order-summary-compact-content {
            background: var(--color-surface-elevated);
            border-radius: var(--border-radius-sm);
            padding: var(--spacing-sm);
            border: 1px solid var(--color-primary-light);
            box-shadow: var(--shadow-xs);
        }

        /* Fila compacta para subtotal e IGV */
        .order-summary-compact-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-xs);
            margin-bottom: var(--spacing-xs);
        }

        /* Item individual en la fila */
        .order-summary-compact-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.7rem;
            color: var(--color-gray-600);
            padding: var(--spacing-xs);
            background: var(--color-surface-hover);
            border-radius: var(--border-radius-xs);
        }

        .order-summary-compact-label {
            font-weight: 500;
        }

        .order-summary-compact-value {
            font-weight: 600;
            color: var(--color-gray-900);
        }

        /* Total prominente compacto */
        .order-summary-compact-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--color-primary);
            padding: var(--spacing-sm);
            border-top: 1px solid var(--color-primary-light);
            margin-top: var(--spacing-xs);
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-surface-elevated) 100%);
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-xs);
        }

        .order-summary-compact-total-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .order-summary-compact-total-value {
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--color-primary);
        }

        /* ===== ESTILOS COMPACTOS PARA NOTAS DEL PEDIDO ===== */

        /* Contenedor de notas compacto */
        .order-note-compact {
            margin-top: var(--spacing-sm);
        }

        /* Label de notas compacto */
        .order-note-compact-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 0.7rem;
            color: var(--color-primary);
            margin-bottom: var(--spacing-xs);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--color-primary-light);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
        }

        .order-note-compact-label svg {
            margin-right: var(--spacing-xs);
        }

        /* Input de notas compacto */
        .order-note-compact-input {
            width: 100%;
            padding: var(--spacing-sm);
            border: 1px solid var(--color-primary-light);
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
            color: var(--color-gray-900);
            background: var(--color-surface-elevated);
            transition: all var(--transition-fast);
            resize: vertical;
            min-height: 60px;
            font-family: var(--font-family-base);
        }

        .order-note-compact-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(60, 80, 224, 0.1);
            background: white;
        }

        .order-note-compact-input::placeholder {
            color: var(--color-gray-400);
            font-style: italic;
            font-size: 0.7rem;
        }

        /* ===== MODO OSCURO PARA RESUMEN Y NOTAS COMPACTOS ===== */
        .dark .order-summary-compact {
            background: linear-gradient(180deg, var(--color-surface-elevated) 0%, var(--color-surface-hover) 100%);
            border-color: var(--color-primary-light);
        }

        .dark .order-summary-compact-title {
            background: var(--color-primary-light);
            color: white;
        }

        .dark .order-summary-compact-content {
            background: var(--color-surface-elevated);
            border-color: var(--color-primary-light);
        }

        .dark .order-summary-compact-item {
            background: var(--color-surface-hover);
            color: var(--color-gray-600);
        }

        .dark .order-summary-compact-value {
            color: var(--color-gray-900);
        }

        .dark .order-summary-compact-total {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-surface-elevated) 100%);
            color: white;
        }

        .dark .order-summary-compact-total-label,
        .dark .order-summary-compact-total-value {
            color: white;
        }

        .dark .order-note-compact-label {
            background: var(--color-primary-light);
            color: white;
        }

        .dark .order-note-compact-input {
            background: var(--color-surface-elevated);
            border-color: var(--color-primary-light);
            color: var(--color-gray-900);
        }

        .dark .order-note-compact-input:focus {
            background: var(--color-surface-hover);
            border-color: var(--color-primary-light);
        }
    </style>
    <!-- Barra superior (Simplificada para enfoque POS) -->
    {{-- <header class="flex-shrink-0 bg-white shadow-sm dark:bg-gray-800">
        <div class="max-w-full px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-blue-600 dark:text-blue-400">POS Restaurante</span>
                </div>
                <div class="flex items-center space-x-4">
                     <span class="px-3 py-1.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                           @if($tableId) data-table-id="{{ $tableId }}" @endif>
                        @if($table)
                            Mesa: {{ $table->number }} | {{ ucfirst($table->location ?? 'General') }}
                        @else
                            Venta Rápida
                        @endif
                    </span>
                     <a href="{{ route('pos.invoices.list') }}" title="Ver Comprobantes" class="p-2 text-gray-500 transition-colors duration-200 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </a>
                     <a href="{{ route('tables.map') }}" title="Mapa de Mesas" class="p-2 text-gray-500 transition-colors duration-200 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </a>
                    <button type="button" title="Configuración" class="p-2 text-gray-500 transition-colors duration-200 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </header> --}}

     <!-- Header simplificado y responsivo -->
     <header class="z-10 flex-shrink-0 bg-white shadow-sm dark:bg-gray-800">
        <div class="max-w-full px-2 mx-auto sm:px-4 lg:px-6">
            <div class="flex flex-col py-2 sm:flex-row sm:justify-between sm:items-center sm:h-14">
                <div class="flex items-center justify-between mb-2 sm:mb-0">
                    <div class="flex items-center">
                        <span class="text-lg font-semibold text-gray-800 sm:text-xl dark:text-gray-200">Sistema POS</span>
                        <span class="hidden mx-2 text-gray-300 dark:text-gray-600 sm:inline">|</span>
                        <span class="px-2 py-1 ml-2 text-xs font-medium text-blue-800 bg-blue-100 rounded-md sm:px-3 sm:text-sm dark:bg-blue-900 dark:text-blue-200 ring-1 ring-inset ring-blue-200 dark:ring-blue-700 sm:ml-0">
                            @if($table)
                                Mesa: {{ $table->number }} <span class="hidden lg:inline">| {{ ucfirst($table->location ?? 'General') }}</span>
                            @else
                                Venta Rápida
                            @endif
                        </span>
                    </div>
                    <!-- Botón de menú móvil (si fuera necesario en el futuro) -->
                    <button class="p-2 text-gray-500 sm:hidden hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-1 px-1 py-1 pb-2 -mx-1 overflow-x-auto sm:gap-2 sm:pb-0 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                    <!-- Botón para ir al mapa de mesas -->
                    <a
                        href="{{ url('/tables') }}"
                        title="Ver mapa de mesas del restaurante"
                        class="flex-shrink-0 px-2 sm:px-3 py-2 rounded-md text-white bg-green-600 hover:bg-green-700 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 border-2 border-green-400 relative z-10 min-h-[44px] flex items-center"
                        style="box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);"
                    >
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="hidden font-bold sm:inline">Mesas</span>

                        </span>
                    </a>

                    <a href="{{ url('/admin') }}" title="Ir al panel de administración" class="flex-shrink-0 px-2 sm:px-3 py-2 rounded-md text-white bg-green-600 hover:bg-green-700 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 border-2 border-green-400 relative z-10 min-h-[44px] flex items-center" style="box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <span class="hidden sm:inline">Administración</span>

                        </span>
                    </a>
                    <a href="{{ url('/dashboard') }}" title="Ver reportes y estadísticas" class="flex-shrink-0 px-2 sm:px-3 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 min-h-[44px] flex items-center">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span class="hidden sm:inline">Reportes</span>

                        </span>
                    </a>

                </div>
            </div>
        </div>
    </header>

    <!-- Main Content - Three Panel Layout Responsivo -->
    <div class="pos-main">
        <!-- Panel Izquierdo - Categorías -->
        <div class="pos-categories dark:bg-gray-800 dark:border-gray-700">
            <div class="p-3">
                <h2 class="flex items-center px-2 mb-3 text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Categorías
                </h2>
                <nav class="space-y-1">
                    @php
                        // Paleta de colores CSS DIRECTOS - GARANTIZA VISIBILIDAD TOTAL
                        $categoryColors = [
                            // TailAdmin Primary - Azul corporativo
                            ['bg_color' => '#3B82F6', 'selected_bg_color' => '#2563EB', 'badge_bg' => '#DBEAFE', 'badge_text' => '#1E40AF'],
                            // Rojo - Excelente contraste
                            ['bg_color' => '#EF4444', 'selected_bg_color' => '#DC2626', 'badge_bg' => '#FEE2E2', 'badge_text' => '#991B1B'],
                            // Naranja - Buen contraste
                            ['bg_color' => '#F97316', 'selected_bg_color' => '#EA580C', 'badge_bg' => '#FED7AA', 'badge_text' => '#9A3412'],
                            // Ámbar oscuro
                            ['bg_color' => '#D97706', 'selected_bg_color' => '#B45309', 'badge_bg' => '#FEF3C7', 'badge_text' => '#92400E'],
                            // Verde - Excelente contraste
                            ['bg_color' => '#22C55E', 'selected_bg_color' => '#16A34A', 'badge_bg' => '#DCFCE7', 'badge_text' => '#166534'],
                            // TailAdmin Secondary - Cian oscuro
                            ['bg_color' => '#0891B2', 'selected_bg_color' => '#0E7490', 'badge_bg' => '#CFFAFE', 'badge_text' => '#155E75'],
                            // Índigo - Excelente contraste
                            ['bg_color' => '#6366F1', 'selected_bg_color' => '#4F46E5', 'badge_bg' => '#E0E7FF', 'badge_text' => '#3730A3'],
                            // Púrpura - Buen contraste
                            ['bg_color' => '#A855F7', 'selected_bg_color' => '#9333EA', 'badge_bg' => '#F3E8FF', 'badge_text' => '#6B21A8'],
                            // Rosa - Buen contraste
                            ['bg_color' => '#EC4899', 'selected_bg_color' => '#DB2777', 'badge_bg' => '#FCE7F3', 'badge_text' => '#BE185D'],
                            // Esmeralda - Excelente contraste
                            ['bg_color' => '#10B981', 'selected_bg_color' => '#059669', 'badge_bg' => '#D1FAE5', 'badge_text' => '#065F46'],
                            // Teal - Excelente contraste
                            ['bg_color' => '#14B8A6', 'selected_bg_color' => '#0D9488', 'badge_bg' => '#CCFBF1', 'badge_text' => '#134E4A'],
                            // TailAdmin Sidebar - Gris oscuro corporativo
                            ['bg_color' => '#475569', 'selected_bg_color' => '#334155', 'badge_bg' => '#F1F5F9', 'badge_text' => '#1E293B'],
                            // Amarillo oscuro
                            ['bg_color' => '#CA8A04', 'selected_bg_color' => '#A16207', 'badge_bg' => '#FEF3C7', 'badge_text' => '#92400E'],
                            // Lima oscuro
                            ['bg_color' => '#65A30D', 'selected_bg_color' => '#4D7C0F', 'badge_bg' => '#ECFCCB', 'badge_text' => '#365314'],
                            // Violeta
                            ['bg_color' => '#8B5CF6', 'selected_bg_color' => '#7C3AED', 'badge_bg' => '#EDE9FE', 'badge_text' => '#5B21B6'],
                            // Fucsia
                            ['bg_color' => '#D946EF', 'selected_bg_color' => '#C026D3', 'badge_bg' => '#FAE8FF', 'badge_text' => '#A21CAF'],
                            // Rosa intenso
                            ['bg_color' => '#F43F5E', 'selected_bg_color' => '#E11D48', 'badge_bg' => '#FFE4E6', 'badge_text' => '#BE123C'],
                            // Gris
                            ['bg_color' => '#4B5563', 'selected_bg_color' => '#374151', 'badge_bg' => '#F3F4F6', 'badge_text' => '#1F2937'],
                            // Sky
                            ['bg_color' => '#0EA5E9', 'selected_bg_color' => '#0284C7', 'badge_bg' => '#E0F2FE', 'badge_text' => '#0C4A6E'],
                            // Zinc
                            ['bg_color' => '#52525B', 'selected_bg_color' => '#3F3F46', 'badge_bg' => '#F4F4F5', 'badge_text' => '#18181B'],
                        ];

                        // FALLBACK: Color por defecto garantizado
                        $defaultColor = ['bg_color' => '#4B5563', 'selected_bg_color' => '#374151', 'badge_bg' => '#F3F4F6', 'badge_text' => '#1F2937'];
                    @endphp
                    @foreach ($categories as $index => $category)
                        @php
                            // Asegurar que siempre tengamos un color válido
                            $colorIndex = $index % count($categoryColors);
                            $colors = isset($categoryColors[$colorIndex]) ? $categoryColors[$colorIndex] : $defaultColor;
                        @endphp
                        <button
                            wire:click="loadProductsByCategory('{{ $category->id }}')"
                            class="w-full py-2 px-3 text-left rounded-md transition-all duration-200 text-sm flex items-center justify-between group shadow-sm hover:shadow-md font-medium"
                            style="background-color: {{ $selectedCategoryId == $category->id ? $colors['selected_bg_color'] : $colors['bg_color'] }} !important;
                                   color: white !important;
                                   {{ $selectedCategoryId == $category->id ? 'box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.6);' : '' }}"
                        >
                            <span class="font-medium truncate">{{ $category->name }}</span>
                             <span class="text-xs font-normal ml-2 px-1.5 py-0.5 rounded-full"
                                   style="background-color: {{ $selectedCategoryId == $category->id ? $colors['badge_bg'] : 'rgba(255, 255, 255, 0.2)' }} !important;
                                          color: {{ $selectedCategoryId == $category->id ? $colors['badge_text'] : 'white' }} !important;">
                                {{ $category->products_count }}
                            </span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Panel Central - Productos -->
        <div class="pos-products dark:bg-gray-900/50">
            <div class="p-4">
                 <!-- Barra de búsqueda y título -->
                <div class="sticky top-0 z-10 flex items-center justify-between px-4 py-3 mb-4 -mx-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700/50">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        {{ $categories->find($selectedCategoryId)?->name ?? 'Productos' }}
                            </h2>
                    <div class="relative w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                                <input
                            type="search"
                                    wire:model.live.debounce.300ms="searchQuery"
                                    placeholder="Buscar productos..."
                            class="w-full py-2 pl-10 pr-4 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        />
                    </div>
                </div>

                <!-- Grid de Productos Responsivo -->
                 <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @forelse ($products as $product)
                        <div
                            wire:key="product-{{ $product->id }}"
                            wire:click="addToCart({{ $product->id }})"
                            class="product-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden group transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border border-gray-200 dark:border-gray-700 flex flex-col relative scale-hover fade-in pos-livewire {{ !$product->available ? 'opacity-75' : '' }}"
                        >
                            <div class="product-image-container {{ !$product->available ? 'product-image-unavailable' : '' }}">
                                @if ($product->image_path)
                                    <img
                                        src="{{ asset('storage/' . $product->image_path) }}"
                                        alt="{{ $product->name }}"
                                        class="product-image"
                                        loading="lazy"
                                        wire:key="product-image-{{ $product->id }}"
                                        onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'product-image-fallback\'><div class=\'product-initials\'>{{ strtoupper(substr($product->name, 0, 2)) }}</div></div>';"
                                    >
                                @else
                                    <div class="product-image-fallback">
                                        <div class="product-initials">
                                            {{ strtoupper(substr($product->name, 0, 2)) }}
                                        </div>
                                        @if($product->category ?? null)
                                            <div class="product-category-badge">
                                                {{ $product->category->name }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col justify-between flex-grow p-3">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-800 dark:text-white min-h-[2.5rem] line-clamp-2">{{ $product->name }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $product->category->name ?? 'Sin categoría' }}</p>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                        S/ {{ number_format($product->sale_price, 2) }}
                                    </span>
                                    <button class="p-1 text-blue-600 transition-colors duration-150 rounded-full bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-800/50 dark:text-blue-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-16 text-gray-500 col-span-full dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"> <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 10l4 4m0-4l-4 4" /> </svg>
                            <p class="text-base font-medium">No se encontraron productos</p>
                            <p class="mt-1 text-sm">Intenta con otra categoría o búsqueda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Panel Derecho - Pedido -->
        <div class="pos-cart dark:bg-gray-800 dark:border-gray-700">
            <!-- Encabezado del pedido compacto -->
            <div class="flex-shrink-0 p-2 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="flex items-center text-sm font-bold text-gray-800 dark:text-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Pedido Actual
                        <span class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-xs">
                            {{ count($cart) }} {{ count($cart) === 1 ? 'item' : 'items' }}
                        </span>
                    </h2>
                    <button
                        wire:click="clearCart"
                        type="button"
                        class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded flex items-center"
                         {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Vaciar
                    </button>
                </div>

                <!-- ✨ Nuevo: Selector de número de comensales -->
                <div class="mb-2">
                    <label for="number_of_guests" class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.124-1.282-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.124-1.282.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Nº de Comensales
                    </label>
                    <input
                        id="number_of_guests"
                        type="number"
                        wire:model.blur="numberOfGuests"
                        min="1"
                        max="50"
                        class="mt-1 block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Nº de personas"
                    >
                </div>

                <!-- Selector de tipo de servicio compacto -->
                <div class="mb-1">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300">Tipo de Servicio</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <button
                            type="button"
                            wire:click="setServiceType('dine_in')"
                            class="px-1 py-1 rounded-md text-xs font-medium transition-all duration-200 flex flex-col items-center justify-center border
                                {{ $serviceType === 'dine_in'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 border-blue-400 dark:border-blue-700'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="font-medium">En Local</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setServiceType('takeout')"
                            class="px-1 py-1 rounded-md text-xs font-medium transition-all duration-200 flex flex-col items-center justify-center border
                                {{ $serviceType === 'takeout'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300 border-green-400 dark:border-green-700'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="font-medium">Para Llevar</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setServiceType('delivery')"
                            class="px-1 py-1 rounded-md text-xs font-medium transition-all duration-200 flex flex-col items-center justify-center border
                                {{ $serviceType === 'delivery'
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 border-red-400 dark:border-red-700'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                            <span class="font-medium">Delivery</span>
                        </button>
                    </div>
                </div>

                <!-- Botones principales con diseño responsivo -->
                <div class="grid grid-cols-3 gap-1 sm:gap-2 mb-2">
                    <!-- Botón Comanda (visible para todos) -->
                    <button
                        onclick="abrirComanda()"
                        type="button"
                        class="px-2 py-2 sm:py-3 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed min-h-[44px] scale-hover"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span class="text-xs font-medium sm:text-sm">Comanda</span>
                    </button>

                    <!-- Botón Facturar (visible para todos, pero con restricción funcional) -->
                    <button
                        onclick="@if(Auth::user()->hasRole('waiter'))
                                    alert('⚠️ Solo un cajero puede facturar la venta. Contacta a un cajero para procesar el pago.');
                                 @else
                                    abrirFactura();
                                 @endif"
                        type="button"
                        class="px-2 py-2 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed min-h-[44px] scale-hover {{ Auth::user()->hasRole('waiter') ? 'opacity-75' : '' }}"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /> </svg>
                        <span class="text-xs font-medium sm:text-sm">Facturar</span>
                        @if(Auth::user()->hasRole('waiter'))
                        <span class="text-xs opacity-75">(Solo Cajero)</span>
                        @endif
                    </button>
                </div>

                <!-- Botones de acciones adicionales responsivos -->
                <div class="grid grid-cols-2 gap-1 mt-1 mb-1 sm:grid-cols-3 sm:gap-2">
                    @if($table && !Auth::user()->hasRole(['waiter', 'cashier']))
                        <!-- Botón Transferir Mesa (no visible para waiter y cashier) -->
                        <button
                            type="button"
                            onclick="abrirModalTransferencia()"
                            class="px-2 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs min-h-[44px] scale-hover"
                            id="btn-transferir-mesa"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /> </svg>
                            <span class="text-xs font-medium">Transferir</span>
                        </button>
                    @endif

                    @if(!Auth::user()->hasRole(['waiter', 'cashier']))
                    <!-- Botón Cancelar Pedido (no visible para waiter y cashier) -->
                    <button
                        @if(count($cart) > 0)
                        onclick="if(confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) { @this.cancelOrder(); }"
                        @else
                        onclick="alert('No hay productos en el carrito para cancelar.')"
                        @endif
                        class="px-2 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs min-h-[44px] scale-hover {{ count($cart) === 0 ? 'opacity-50' : '' }} {{ $table ? 'col-span-1' : 'col-span-2' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /> </svg>
                        <span class="text-xs font-medium">Cancelar</span>
                    </button>
                    @endif

                    <!-- Botón Liberar Mesa (no visible para waiter y cashier) -->
                    @if($table && ($serviceType === 'dine_in' || !$serviceType) && !Auth::user()->hasRole(['waiter', 'cashier']))
                    <button
                        onclick="if(confirm('¿Estás seguro de que deseas liberar esta mesa? Esta acción cambiará el estado de la mesa a disponible y cancelará cualquier orden asociada. Esta acción es solo para casos excepcionales cuando un cliente se va sin consumir.')) { @this.releaseTable(); }"
                        class="px-2 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs min-h-[44px] scale-hover"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span class="text-xs font-medium">Liberar Mesa</span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Lista de productos en el pedido - Diseño compacto optimizado -->
            <div class="cart-standard">
                <!-- Lista de productos con diseño compacto -->
                @forelse ($cart as $item)
                    <div wire:key="cart-item-{{ $item['id'] }}" class="cart-item-compact">
                        <!-- Fila principal con información esencial -->
                        <div class="cart-item-main-row">
                            <!-- Nombre del producto -->
                            <div class="cart-item-name-compact">{{ $item['name'] }}</div>

                            <!-- Controles de cantidad compactos -->
                            <div class="cart-item-quantity-compact">
                                <button
                                    wire:click="updateCartItemQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                    type="button"
                                    class="quantity-btn-compact quantity-btn-minus"
                                >&minus;</button>
                                <span class="quantity-value-compact">{{ $item['quantity'] }}</span>
                                <button
                                    wire:click="updateCartItemQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                    type="button"
                                    class="quantity-btn-compact quantity-btn-plus"
                                >+</button>
                            </div>

                            <!-- Subtotal prominente -->
                            <div class="cart-item-subtotal-compact">
                                S/ {{ number_format($item['subtotal'], 2) }}
                            </div>

                            <!-- Botón eliminar compacto -->
                            <button
                                wire:click="removeFromCart('{{ $item['id'] }}')"
                                type="button"
                                class="cart-item-remove-compact"
                                title="Eliminar del carrito"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></path></svg>
                            </button>
                        </div>

                        <!-- Fila secundaria con precio unitario -->
                        <div class="cart-item-secondary-row">
                            <div class="cart-item-price-compact">
                                Precio: <span>S/ {{ number_format($item['price'], 2) }}</span>
                                @can('admin')
                                <button type="button" wire:click="openEditPriceModal('{{ $item['id'] }}')" class="edit-price-btn-compact" title="Editar precio">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                @endcan
                            </div>
                        </div>

                        <!-- Notas del producto si existen -->
                        @if(!empty($item['notes']))
                        <div class="cart-item-note-compact">
                            <span>Nota: </span>{{ $item['notes'] }}
                        </div>
                        @endif
                    </div>
                @empty
                    <!-- Estado de carrito vacío -->
                    <div class="cart-empty-standard">
                        <svg xmlns="http://www.w3.org/2000/svg" class="cart-empty-icon-standard" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <p>El carrito está vacío</p>
                        <p>Agrega productos para comenzar tu pedido</p>
                    </div>
                @endforelse
            </div>

            <!-- Resumen del pedido - Diseño compacto optimizado -->
            <div class="order-summary-compact">
                <!-- Título del resumen compacto -->
                <div class="order-summary-compact-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    RESUMEN DEL PEDIDO
                </div>

                <!-- Resumen del pedido - Contenido compacto -->
                <div class="order-summary-compact-content">
                    <!-- Subtotal e IGV INCLUIDO (CORRECTO) -->
                    <div class="order-summary-compact-row">
                        <div class="order-summary-compact-item">
                            <span class="order-summary-compact-label">Subtotal:</span>
                            <span class="order-summary-compact-value">S/ {{ number_format($this->getCartSubtotal(), 2) }}</span>
                        </div>
                        <div class="order-summary-compact-item">
                            <span class="order-summary-compact-label">IGV (18%):</span>
                            <span class="order-summary-compact-value">S/ {{ number_format($this->getCartTax(), 2) }}</span>
                        </div>
                    </div>

                    <!-- Total prominente (precio original con IGV incluido) -->
                    <div class="order-summary-compact-total">
                        <span class="order-summary-compact-total-label">TOTAL:</span>
                        <span class="order-summary-compact-total-value">S/ {{ number_format($cartTotal, 2) }}</span>
                    </div>

                    <!-- Nota aclaratoria -->
                    <div style="text-align: center; font-size: 0.7rem; color: #6b7280; font-style: italic; margin-top: 0.5rem;">
                        * Precios incluyen IGV
                    </div>
                </div>

                <!-- Notas del pedido compactas -->
                <div class="order-note-compact">
                    <label for="customerNote" class="order-note-compact-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        NOTA PARA EL PEDIDO
                    </label>
                    <textarea
                        id="customerNote"
                        wire:model="customerNote"
                        placeholder="Ej: Sin ají, bien cocido..."
                        class="order-note-compact-input"
                        rows="2"
                    ></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edición de precios (solo administradores) -->
     @if($showEditPriceModal)
     <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-600/75 dark:bg-gray-900/80"
          x-data="{ open: @entangle('showEditPriceModal') }"
          x-show="open"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          {{-- style="display: none;" --}}
    >
        <div class="w-full max-w-md overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800" @click.away="open = false">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between mb-4">
                     <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Precio</h3>
                     <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                     </button>
                 </div>

                <div class="mb-4 text-sm">
                    <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Producto</label>
                     <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $cart[$editingProductId]['name'] ?? 'N/A' }}</p>
                </div>

                <div class="mb-5">
                    <label for="newPrice" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Nuevo precio</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                         <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                             <span class="text-gray-500 dark:text-gray-400 sm:text-sm">S/</span>
                         </div>
                         <input
                            type="number"
                            id="newPrice"
                            wire:model="newPrice"
                            step="0.01"
                            min="0.01"
                             class="block w-full py-2 pl-8 pr-4 transition border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="0.00"
                        >
                    </div>
                    {{-- Añadir validación de error si es necesario --}}
                 </div>
            </div>
             <div class="flex justify-end gap-3 px-6 py-3 bg-gray-50 dark:bg-gray-700/50">
                <button
                    type="button"
                    @click="open = false"
                     class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-md shadow-sm dark:border-gray-500 dark:text-gray-200 dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                > Cancelar </button>
                <button
                    type="button"
                    wire:click="saveNewPrice"
                     class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    wire:loading.attr="disabled" wire:target="saveNewPrice"
                >
                    <span wire:loading.remove wire:target="saveNewPrice">Guardar Precio</span>
                    <span wire:loading wire:target="saveNewPrice">Guardando...</span>
                    </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para Comanda -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-600/75 dark:bg-gray-900/80 backdrop-blur-sm"
         x-data="{ url: @entangle('commandUrl') }"
         x-show="$wire.showCommandModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
    >
        <div class="w-full max-w-4xl overflow-hidden bg-white shadow-2xl dark:bg-gray-800 rounded-xl" @click.away="$wire.showCommandModal = false">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="flex items-center text-lg font-medium text-gray-900 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Comanda
                </h3>
                <button @click="$wire.showCommandModal = false" class="p-1 text-gray-400 transition-all duration-200 bg-gray-100 rounded-full hover:text-gray-600 dark:hover:text-gray-300 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 h-[70vh]">
                <iframe id="commandFrame" x-bind:src="url" class="w-full h-full border-0 rounded-md bg-gray-50 dark:bg-gray-700/30"></iframe>
            </div>
        </div>
    </div>

    <!-- Modal para Pre-Cuenta -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-600/75 dark:bg-gray-900/80 backdrop-blur-sm"
         x-data="{ url: @entangle('preBillUrl') }"
         x-show="$wire.showPreBillModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
    >
        <div class="w-full max-w-4xl overflow-hidden bg-white shadow-2xl dark:bg-gray-800 rounded-xl" @click.away="$wire.showPreBillModal = false">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="flex items-center text-lg font-medium text-gray-900 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Pre-Cuenta
                </h3>
                <button @click="$wire.showPreBillModal = false" class="p-1 text-gray-400 transition-all duration-200 bg-gray-100 rounded-full hover:text-gray-600 dark:hover:text-gray-300 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 h-[70vh]">
                <iframe id="preBillFrame" x-bind:src="url" class="w-full h-full border-0 rounded-md bg-gray-50 dark:bg-gray-700/30"></iframe>
            </div>
        </div>
    </div>

    <!-- Modal para Facturación -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-600/75 dark:bg-gray-900/80 backdrop-blur-sm"
         x-data="{ url: @entangle('invoiceUrl') }"
         x-show="$wire.showInvoiceModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
    >
        <div class="w-full max-w-5xl overflow-hidden bg-white shadow-2xl dark:bg-gray-800 rounded-xl" @click.away="$wire.showInvoiceModal = false">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="flex items-center text-lg font-medium text-gray-900 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Formulario de Facturación
                </h3>
                <button @click="$wire.showInvoiceModal = false" class="p-1 text-gray-400 transition-all duration-200 bg-gray-100 rounded-full hover:text-gray-600 dark:hover:text-gray-300 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    </button>
                </div>
            <div class="px-6 py-4 h-[80vh]">
                <iframe id="invoiceFormFrame" x-bind:src="url" class="w-full h-full border-0 rounded-md bg-gray-50 dark:bg-gray-700/30"></iframe>
            </div>
        </div>
    </div>



    <!-- Modal para Transferir Mesa -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-600/75 dark:bg-gray-900/80 backdrop-blur-sm"
         x-data="{showModal: @entangle('showTransferModal')}"
         x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
         id="modal-transferir-mesa"
         x-init="
            $watch('showModal', value => {
                console.log('Estado del modal de transferencia:', value);
                if (value) {
                    console.log('Modal de transferencia abierto');
                } else {
                    console.log('Modal de transferencia cerrado');
                }
            });
         "
    >
        <div class="w-full max-w-md overflow-hidden bg-white shadow-2xl dark:bg-gray-800 rounded-xl" @click.away="cerrarModalTransferencia()">
            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="flex items-center text-sm font-medium text-gray-900 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transferir Mesa
                </h3>
                <button onclick="cerrarModalTransferencia()" type="button" aria-label="Cerrar modal de transferencia" class="p-1 text-gray-400 transition-all duration-200 bg-gray-100 rounded-full hover:text-gray-600 dark:hover:text-gray-300 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex flex-col h-[400px] overflow-hidden">
                <div class="flex-shrink-0 px-3 py-2">
                    <p class="mb-1 text-xs text-gray-700 dark:text-gray-300">
                        Selecciona los productos que deseas transferir de la mesa <span class="font-semibold">{{ $table ? $table->number : '' }}</span>.
                    </p>
                </div>

                <!-- Lista de productos con checkboxes -->
                <div class="flex-shrink-0 px-3">
                    <div class="overflow-hidden border border-gray-200 rounded-lg dark:border-gray-700">
                        <div class="px-2 py-1 border-b border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="select-all-products"
                                    wire:model="selectAllProductsForTransfer"
                                    wire:change="toggleSelectAllProducts"
                                    class="w-3.5 h-3.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <label for="select-all-products" class="ml-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Seleccionar todos los productos
                                </label>
                            </div>
                        </div>
                        <div class="h-[120px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-1.5 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8">
                                            Sel.
                                        </th>
                                        <th scope="col" class="px-1.5 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th scope="col" class="px-1.5 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">
                                            Cant.
                                        </th>
                                        <th scope="col" class="px-1.5 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                            Precio
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-600">
                                    @forelse($cart as $productId => $item)
                                        <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-1.5 py-1.5 whitespace-nowrap">
                                                <input
                                                    type="checkbox"
                                                    id="product-{{ $productId }}"
                                                    wire:model="selectedProductsForTransfer"
                                                    value="{{ $productId }}"
                                                    wire:change="$refresh"
                                                    onclick="console.log('Checkbox clicked: {{ $productId }}', this.checked); if(this.checked) { window.livewire.find('point-of-sale').set('selectedProductsForTransfer', [...window.livewire.find('point-of-sale').get('selectedProductsForTransfer'), '{{ $productId }}']); } else { window.livewire.find('point-of-sale').set('selectedProductsForTransfer', window.livewire.find('point-of-sale').get('selectedProductsForTransfer').filter(id => id !== '{{ $productId }}')); }"
                                                    class="w-3.5 h-3.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                >
                                            </td>
                                            <td class="px-1.5 py-1.5 whitespace-nowrap">
                                                <div class="text-xs font-medium text-gray-900 dark:text-white truncate max-w-[120px]">{{ $item['name'] }}</div>
                                            </td>
                                            <td class="px-1.5 py-1.5 whitespace-nowrap text-right text-xs text-gray-900 dark:text-white">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="px-1.5 py-1.5 whitespace-nowrap text-right text-xs text-gray-900 dark:text-white">
                                                S/ {{ number_format($item['price'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-1.5 py-1.5 text-center text-xs text-gray-500 dark:text-gray-400">
                                                No hay productos en el carrito
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumen de selección -->
                <div class="flex items-center justify-between flex-shrink-0 px-3 py-1">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ count($selectedProductsForTransfer) }}</span> de <span class="font-medium">{{ count($cart) }}</span> productos seleccionados
                        <button
                            wire:click="refreshSelectedProducts"
                            class="ml-1 text-xs text-blue-500 hover:text-blue-700"
                            title="Actualizar selección"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                    @if(!empty($selectedProductsForTransfer))
                        <div class="text-xs text-green-600 dark:text-green-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Listo para transferir
                        </div>
                    @endif
                </div>

                <!-- Selección de mesa destino -->
                <div class="flex-shrink-0 px-3">
                    <h3 class="mb-1 text-xs font-medium text-gray-800 dark:text-gray-200">Selecciona la mesa destino:</h3>
                </div>

                <div class="flex-grow px-3 pb-2 overflow-hidden">
                    <div class="h-full overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800 p-0.5">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5">
                            @foreach($availableTables as $availableTable)
                                <button
                                    onclick="transferirMesa({{ $availableTable->id }})"
                                    class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-1.5 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200 flex flex-col items-center {{ empty($selectedProductsForTransfer) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ empty($selectedProductsForTransfer) ? 'disabled' : '' }}
                                    x-data="{}"
                                    x-init="
                                        $wire.on('selectedProductsUpdated', (event) => {
                                            console.log('Productos seleccionados actualizados:', event);
                                            if (event.count > 0) {
                                                $el.disabled = false;
                                                $el.classList.remove('opacity-50', 'cursor-not-allowed');
                                            } else {
                                                $el.disabled = true;
                                                $el.classList.add('opacity-50', 'cursor-not-allowed');
                                            }
                                        });
                                    "
                                >
                                    <div class="table-visual {{ $availableTable->shape === 'square' ? 'table-square' : 'table-round' }} bg-green-100 dark:bg-green-900 border-green-500 mb-0.5" style="width: 32px; height: 32px;">
                                        <span class="text-xs font-bold">{{ $availableTable->number }}</span>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200">Mesa {{ $availableTable->number }}</p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ ucfirst($availableTable->location) }}</p>
                                    </div>
                                </button>
                            @endforeach

                            @if(count($availableTables) === 0)
                                <div class="flex flex-col items-center justify-center py-4 text-center col-span-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">No hay mesas disponibles para transferir.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-3 py-1.5 flex justify-end gap-2 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    onclick="cerrarModalTransferencia()"
                    class="inline-flex justify-center py-1 px-2.5 border border-gray-300 dark:border-gray-500 shadow-sm text-xs font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors duration-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Delivery -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-600/75 dark:bg-gray-900/80"
        x-data="{
            get isOpen() {
                return $wire.showDeliveryModal
            }
        }"
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
        @click.self="$event.stopPropagation()"
    >
        <div
            class="w-full max-w-4xl overflow-hidden bg-white shadow-2xl dark:bg-gray-800 rounded-xl"
            @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-red-900/30 dark:to-gray-800">
                <h3 class="flex items-center text-lg font-medium text-gray-900 dark:text-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    Pedido Delivery
                </h3>
                <button
                    wire:click="closeDeliveryModal"
                    class="p-1 text-gray-400 transition-all duration-200 bg-gray-100 rounded-full hover:text-gray-600 dark:hover:text-gray-300 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
                    onclick="console.log('Botón cerrar clickeado'); $wire.closeDeliveryModal();"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <p class="mb-2 text-gray-700 dark:text-gray-300">
                        Ingresa los datos del cliente y la dirección de entrega para el pedido de delivery.
                    </p>
                    <!-- Mensaje de alerta principal - Exactamente como en la imagen -->
                    <div class="p-4 mb-4 border-l-4 border-yellow-400 bg-yellow-50 dark:bg-yellow-900/30">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-700 dark:text-yellow-200">
                                    Los pedidos de delivery requieren un cliente registrado y una dirección de entrega.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Columna izquierda - Datos del cliente -->
                    <div>
                        <h4 class="flex items-center mb-3 font-medium text-gray-800 text-md dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Datos del Cliente
                        </h4>

                        <div class="space-y-4">
                            <!-- Búsqueda de cliente por teléfono -->
                            <div>
                                <label for="customerPhone" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                                <div class="flex">
                                    <input
                                        type="text"
                                        id="customerPhone"
                                        wire:model="customerPhone"
                                        placeholder="Número de teléfono"
                                        class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-l-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        onkeypress="if(event.keyCode === 13) { event.preventDefault(); buscarClientePorTelefono(); }"
                                    />
                                    <button
                                        onclick="buscarClientePorTelefono()"
                                        id="search-customer-btn"
                                        class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg transition-colors duration-200 flex items-center justify-center min-w-[40px]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5 animate-spin loading-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Busque clientes por su número de teléfono</p>

                                <!-- Mensaje de cliente no encontrado (inicialmente oculto) -->
                                <div id="customer-not-found" class="hidden p-3 mt-2 border-l-4 border-yellow-400 bg-yellow-50 dark:bg-yellow-900/30 rounded-r-md">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Cliente no encontrado</h3>
                                            <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                                <p>No se encontró ningún cliente con el teléfono <span id="not-found-phone" class="font-semibold"></span>.</p>
                                                <p class="mt-1">Por favor, complete los datos para registrar un nuevo cliente.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Mensaje de cliente encontrado (inicialmente oculto) -->
                                <div id="customer-found" class="hidden p-3 mt-2 border-l-4 border-green-400 bg-green-50 dark:bg-green-900/30 rounded-r-md">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Cliente encontrado</h3>
                                            <div class="mt-1 text-sm text-green-700 dark:text-green-300">
                                                <p>Se ha cargado la información de <span id="found-name" class="font-semibold"></span>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del cliente -->
                            <div>
                                <label for="customerName" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Nombre / Razón Social</label>
                                <input
                                    type="text"
                                    id="customerName"
                                    wire:model="customerName"
                                    placeholder="Nombre completo o razón social"
                                    class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <!-- Documento (opcional) -->
                            <div class="flex space-x-2">
                                <div class="w-1/3">
                                    <label for="customerDocumentType" class="block mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Tipo (opcional)</label>
                                    <select
                                        id="customerDocumentType"
                                        wire:model="customerDocumentType"
                                        class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="CE">CE</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                                <div class="w-2/3">
                                    <label for="customerDocument" class="block mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Documento (opcional)</label>
                                    <input
                                        type="text"
                                        id="customerDocument"
                                        wire:model="customerDocument"
                                        placeholder="Número de documento"
                                        class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>

                            <div>
                                <button
                                    wire:click="saveCustomer"
                                    id="save-customer-btn"
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 flex items-center justify-center font-medium shadow-md hover:shadow-lg hover:-translate-y-0.5"
                                    x-data="{
                                        isDisabled: function() {
                                            // Verificar si falta información del cliente
                                            return !$wire.customerName || !$wire.customerPhone;
                                        },
                                        isLoading: false
                                    }"
                                    x-on:click="
                                        console.log('Botón Guardar Cliente clickeado');
                                        if (!$wire.customerName || !$wire.customerPhone) {
                                            console.log('Faltan datos del cliente');
                                            $wire.dispatch('notification', {
                                                type: 'error',
                                                title: 'Error',
                                                message: 'Debes ingresar el nombre y teléfono del cliente para guardarlo.',
                                                showModal: true
                                            });
                                            return false;
                                        }
                                        console.log('Datos del cliente completos, guardando...');
                                        isLoading = true;

                                        // Mostrar feedback inmediato
                                        Swal.fire({
                                            title: 'Guardando Cliente...',
                                            text: 'Por favor espere',
                                            icon: 'info',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            showConfirmButton: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });

                                        // Resetear loading después de 3 segundos
                                        setTimeout(() => {
                                            isLoading = false;
                                            Swal.close();
                                        }, 3000);
                                    "
                                    x-bind:class="{
                                        'opacity-50 cursor-not-allowed': isDisabled() || isLoading,
                                        'bg-gray-500': isLoading
                                    }"
                                    x-bind:disabled="isDisabled() || isLoading"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="!isLoading">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <svg x-show="isLoading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isLoading ? 'Guardando...' : 'Guardar Información del Cliente'"></span>
                                </button>
                                <p class="mt-1 text-xs text-center text-gray-500 dark:text-gray-400">
                                    Solo se requiere nombre y teléfono para registrar un cliente
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Datos de entrega -->
                    <div>
                        <h4 class="flex items-center mb-3 font-medium text-gray-800 text-md dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Datos de Entrega
                        </h4>

                        <div class="space-y-4">
                            <div>
                                <label for="deliveryAddress" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Dirección de Entrega</label>
                                <div class="flex space-x-2">
                                    <input
                                        type="text"
                                        id="deliveryAddress"
                                        wire:model="deliveryAddress"
                                        placeholder="Dirección completa"
                                        class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                    <button
                                        type="button"
                                        onclick="openMapModal();"
                                        class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 flex items-center shadow-md hover:shadow-lg hover:-translate-y-0.5"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Ubica en Mapa
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Ingrese la dirección o use el mapa para ubicar la posición exacta
                                </p>
                            </div>

                            <div>
                                <label for="deliveryReferences" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Referencias</label>
                                <textarea
                                    id="deliveryReferences"
                                    wire:model="deliveryReferences"
                                    placeholder="Referencias para ubicar la dirección (color de casa, puntos de referencia, etc.)"
                                    class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    rows="3"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Agregue referencias que ayuden al repartidor a encontrar la dirección
                                </p>
                            </div>

                            <div class="pt-4 space-y-3">
                                <!-- Botón para agregar productos -->
                                <div x-data="{ cartEmpty: function() { return Object.keys($wire.cart).length === 0; } }">
                                    <button
                                        type="button"
                                        x-on:click="$wire.closeDeliveryModal(); setTimeout(() => { document.getElementById('product-search').focus(); }, 100);"
                                        class="flex items-center justify-center w-full px-4 py-3 font-medium text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Agregar Productos al Carrito
                                    </button>

                                    <div x-show="cartEmpty()" class="mt-2 text-sm text-center text-red-500">
                                        <span class="flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Primero debes agregar productos al carrito
                                        </span>
                                    </div>
                                </div>

                                <!-- Botón para procesar pedido - Mejorado y más visible -->
                                <button
                                    wire:click="processDeliveryOrder"
                                    class="flex items-center justify-center w-full px-4 py-4 text-lg font-bold text-white transition-all duration-300 transform bg-red-600 rounded-lg shadow-lg hover:bg-red-700 hover:shadow-xl hover:-translate-y-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    PROCESAR PEDIDO DE DELIVERY
                                </button>

                                <!-- Mensaje informativo sobre el proceso -->
                                <div class="p-4 mt-3 text-sm text-gray-600 bg-gray-100 border border-gray-200 rounded-lg dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600">
                                    <h5 class="flex items-center mb-2 font-medium text-gray-700 dark:text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Información del Proceso
                                    </h5>
                                    <ul class="pl-1 space-y-1 list-disc list-inside">
                                        <li>Al procesar el pedido, se registrará la información del cliente y la dirección.</li>
                                        <li>Se generará una comanda para la cocina con los productos solicitados.</li>
                                        <li>El pedido quedará registrado en el sistema para su seguimiento.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-3 border-t border-gray-200 bg-gray-50 dark:bg-gray-700/50 dark:border-gray-700">
                <button
                    type="button"
                    wire:click="closeDeliveryModal"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-gray-300 rounded-md shadow-sm dark:border-gray-500 dark:text-gray-200 dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Script para comunicación entre iframes -->
    <script>
        // Función para cambiar entre modo claro y oscuro
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                // Cambiar a modo claro
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';

                // Mostrar notificación
                Livewire.dispatch('notification', {
                    type: 'info',
                    title: 'Modo Claro Activado',
                    message: 'Se ha cambiado al tema claro',
                    timeout: 2000
                });
            } else {
                // Cambiar a modo oscuro
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';

                // Mostrar notificación
                Livewire.dispatch('notification', {
                    type: 'info',
                    title: 'Modo Oscuro Activado',
                    message: 'Se ha cambiado al tema oscuro',
                    timeout: 2000
                });
            }
        }



        // Escuchar eventos de Livewire
        document.addEventListener('livewire:initialized', function() {
            // Escuchar el evento para abrir el modal de delivery después de la renderización
            Livewire.on('open-delivery-modal-after-render', function() {
                console.log('Evento open-delivery-modal-after-render recibido');
                setTimeout(function() {
                    // Forzar la apertura del modal de delivery
                    if (window.Livewire && window.Livewire.find('point-of-sale')) {
                        window.Livewire.find('point-of-sale').set('showDeliveryModal', true);
                        console.log('Modal de delivery forzado a abrir desde JavaScript');
                    }
                }, 300);
            });

            // Escuchar el evento de búsqueda de cliente en progreso
            Livewire.on('search-customer-loading', function(params) {
                console.log('Buscando cliente con teléfono:', params.phone);

                // Mostrar indicador de carga
                const searchButton = document.getElementById('search-customer-btn');
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.add('hidden');
                    searchButton.querySelector('.loading-icon').classList.remove('hidden');
                    searchButton.disabled = true;
                }

                // Ocultar mensajes previos
                document.getElementById('customer-not-found').classList.add('hidden');
                document.getElementById('customer-found').classList.add('hidden');
            });

            // Escuchar el evento de resultado de búsqueda de cliente
            Livewire.on('search-customer-result', function(params) {
                console.log('Resultado de búsqueda de cliente:', params);

                // Restaurar botón de búsqueda
                const searchButton = document.getElementById('search-customer-btn');
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.remove('hidden');
                    searchButton.querySelector('.loading-icon').classList.add('hidden');
                    searchButton.disabled = false;
                }

                if (params.found) {
                    // Cliente encontrado
                    document.getElementById('customer-not-found').classList.add('hidden');

                    // Mostrar mensaje de cliente encontrado
                    const foundMessage = document.getElementById('customer-found');
                    const foundName = document.getElementById('found-name');

                    if (foundMessage && foundName) {
                        foundName.textContent = params.name;
                        foundMessage.classList.remove('hidden');

                        // Ocultar el mensaje después de 5 segundos
                        setTimeout(() => {
                            foundMessage.classList.add('hidden');
                        }, 5000);
                    }
                } else {
                    // Cliente no encontrado
                    document.getElementById('customer-found').classList.add('hidden');

                    // Mostrar mensaje de cliente no encontrado
                    const notFoundMessage = document.getElementById('customer-not-found');
                    const notFoundPhone = document.getElementById('not-found-phone');

                    if (notFoundMessage && notFoundPhone) {
                        notFoundPhone.textContent = params.phone;
                        notFoundMessage.classList.remove('hidden');

                        // Habilitar el botón de guardar cliente
                        const saveButton = document.querySelector('button[wire\\:click="saveCustomer"]');
                        if (saveButton) {
                            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                            saveButton.disabled = false;
                        }

                        // Hacer que el campo de nombre reciba el foco
                        setTimeout(() => {
                            document.getElementById('customerName')?.focus();
                        }, 500);
                    }
                }
            });

            // Escuchar el evento para cerrar el modal de delivery
            Livewire.on('delivery-modal-closed', function() {
                console.log('Evento delivery-modal-closed recibido');

                // Asegurarse de que el modal esté cerrado
                if (window.Livewire && window.Livewire.find('point-of-sale')) {
                    window.Livewire.find('point-of-sale').set('showDeliveryModal', false);
                    console.log('Modal de delivery forzado a cerrar desde JavaScript');

                    // Mostrar una alerta para confirmar que el pedido se procesó correctamente
                    // y redirigir al mapa de mesas después de hacer clic en Aceptar
                    setTimeout(function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pedido Procesado!',
                            text: 'El pedido de delivery ha sido registrado correctamente.',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#10b981'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirigir al mapa de mesas
                                window.location.href = '{{ route("tables.map") }}';
                            }
                        });
                    }, 500);
                }
            });
        });

        // Script de depuración para verificar los botones
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Verificando botones de acción...');

            // Verificar botón de transferencia
            const transferButton = document.querySelector('#btn-transferir-mesa');
            console.log('Botón de transferencia:', transferButton);
            if (transferButton) {
                console.log('Botón de transferencia encontrado');
                console.log('Disabled:', transferButton.disabled);
                console.log('Clase:', transferButton.className);

                // Agregar un listener para verificar que el evento se dispare
                transferButton.addEventListener('click', function() {
                    console.log('Botón de transferencia clickeado');
                });
            } else {
                console.error('Botón de transferencia NO encontrado');
            }

            // Verificar botón de cancelar pedido
            const cancelButton = document.querySelector('a[onclick*="cancelOrder"]');
            console.log('Botón de cancelar pedido:', cancelButton);
            if (cancelButton) {
                console.log('Botón de cancelar pedido encontrado');
                console.log('Clase:', cancelButton.className);
            } else {
                console.error('Botón de cancelar pedido NO encontrado');
            }


        });

        // Listener principal para eventos de factura completada
        window.addEventListener('message', function(event) {
            console.log('Evento recibido:', event.data);

            // Manejar tanto string directo como objeto
            if (event.data === 'invoice-completed' ||
                (event.data && event.data.type === 'invoice-completed')) {

                console.log('Factura completada - Iniciando limpieza del carrito y redirección');

                // Vaciar el carrito
                vaciarCarritoYRedirigirAMesas();

                // Cerrar cualquier modal activo
                if (typeof closeModal === 'function') {
                    closeModal();
                }

                // Mostrar mensaje de éxito
                console.log('Carrito limpiado exitosamente - Redirigiendo a mapa de mesas');
            }
        });

                // ✅ Listener para redirección automática de waiters al mapa de mesas (TEMPORALMENTE DESHABILITADO)
        /*
        document.addEventListener('livewire:init', function () {
            Livewire.on('redirect-to-table-map', function () {
                console.log('🔄 Evento redirect-to-table-map recibido - Verificando si es waiter...');

                // Solo redirigir si es waiter y no estamos ya en el mapa de mesas
                @if(Auth::user()->hasRole('waiter'))
                if (!window.location.href.includes('/admin/mapa-mesas')) {
                    console.log('✅ Es waiter y no está en mapa de mesas - Redirigiendo...');
                    setTimeout(function() {
                        window.location.href = '{{ url("/admin/mapa-mesas") }}';
                    }, 2000); // Dar tiempo para que se abra la ventana de impresión
                } else {
                    console.log('❌ Ya está en mapa de mesas - No redirigir');
                }
                @else
                console.log('❌ No es waiter - No redirigir');
                @endif
            });
        });
        */
    </script>

    <!-- Scripts para abrir ventanas -->
    <script>
        // Función para procesar pedido de delivery
        function procesarPedidoDelivery() {
            // Verificar si hay productos en el carrito
            if (Object.keys(window.Livewire.find('point-of-sale').cart).length === 0) {
                Livewire.dispatch('notification', {
                    type: 'error',
                    title: 'Error',
                    message: 'No hay productos en el carrito. Añade productos para procesar el pedido.',
                    showModal: true
                });
                return;
            }

            // Verificar si falta información del cliente
            if (!window.Livewire.find('point-of-sale').customerName || !window.Livewire.find('point-of-sale').customerPhone) {
                Livewire.dispatch('notification', {
                    type: 'error',
                    title: 'Error',
                    message: 'Debes ingresar el nombre y teléfono del cliente.',
                    showModal: true
                });
                return;
            }

            // Verificar si falta dirección de entrega
            if (!window.Livewire.find('point-of-sale').deliveryAddress) {
                Livewire.dispatch('notification', {
                    type: 'error',
                    title: 'Error',
                    message: 'Debes ingresar la dirección de entrega.',
                    showModal: true
                });
                return;
            }

            // Si todas las validaciones pasan, llamar al método de Livewire
            window.Livewire.find('point-of-sale').processDeliveryOrder();
        }

        function procesarRespuesta(response) {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Error en la petición: ' + response.status);
                });
            }
            return response.json().then(data => {
                if (data.success && data.orderId) {
                    return data.orderId;
                } else {
                    throw new Error(data.message || 'Error al procesar la orden');
                }
            });
        }

        // Función para vaciar el carrito después de generar un comprobante
        // Esta función SOLO debe llamarse cuando se emite un comprobante final (factura/boleta)
        function vaciarCarrito() {
            console.log('🧹 Ejecutando vaciarCarrito() - Limpiando carrito y liberando mesa');

            // Llamar al método clearSale del componente Livewire que limpia el carrito y libera la mesa
            if (window.Livewire) {
                console.log('✅ Livewire disponible - Enviando evento clearSale');

                // Usar dispatch para llamar al método clearSale
                Livewire.dispatch('clearSale');
                console.log('📤 Evento clearSale enviado correctamente');

                // Esperar a que se complete la actualización antes de redirigir
                setTimeout(function() {
                    console.log('🔄 Redirigiendo al mapa de mesas...');
                    // Forzar recarga completa para asegurar que se vea el estado actualizado
                    window.location.href = '{{ url("/tables") }}?refresh=' + Date.now();
                }, 2000); // Aumentar el tiempo para asegurar que se complete la actualización
            } else {
                console.error('❌ Livewire no está disponible');
                // Plan B: Recargar la página
                window.location.href = '{{ url("/tables") }}?refresh=' + Date.now();
            }
        }

        // Función específica para vaciar carrito y redirigir al mapa de mesas de Filament después de facturar
        function vaciarCarritoYRedirigirAMesas() {
            console.log('🧹 Ejecutando vaciarCarritoYRedirigirAMesas() - Limpiando carrito y redirigiendo a Filament');

            // Llamar al método clearSale del componente Livewire que limpia el carrito y libera la mesa
            if (window.Livewire) {
                console.log('✅ Livewire disponible - Enviando evento clearSale');

                // Usar dispatch para llamar al método clearSale
                Livewire.dispatch('clearSale');
                console.log('📤 Evento clearSale enviado correctamente');

                // Esperar a que se complete la actualización antes de redirigir
                setTimeout(function() {
                    console.log('🔄 Redirigiendo al mapa de mesas de Filament...');
                    // Redirigir al mapa de mesas de Filament
                    window.location.href = '{{ url("/admin/mapa-mesas") }}';
                }, 2000); // Dar tiempo para que se complete la actualización
            } else {
                console.error('❌ Livewire no está disponible');
                // Plan B: Redirigir directamente al mapa de mesas de Filament
                window.location.href = '{{ url("/admin/mapa-mesas") }}';
            }
        }

        // Listener duplicado eliminado - se maneja en el listener principal arriba

        // Función para ir al mapa de mesas sin perder el carrito
        function irAMesas() {
            // Mostrar mensaje de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Guardando carrito y actualizando mesa',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Llamar al método de Livewire para guardar el carrito
            Livewire.dispatch('guardarCarritoYRedirigir');
        }

        // Función para buscar cliente por teléfono - Versión KISS mejorada
        function buscarClientePorTelefono() {
            console.log('Iniciando búsqueda de cliente por teléfono');

            // Mostrar indicador de carga
            const searchButton = document.getElementById('search-customer-btn');
            if (searchButton) {
                searchButton.querySelector('.search-icon').classList.add('hidden');
                searchButton.querySelector('.loading-icon').classList.remove('hidden');
                searchButton.disabled = true;
            }

            // Ocultar mensajes previos
            document.getElementById('customer-not-found')?.classList.add('hidden');
            document.getElementById('customer-found')?.classList.add('hidden');

            // Obtener el teléfono
            const phone = document.getElementById('customerPhone').value;

            if (!phone) {
                alert('Ingrese un número de teléfono para buscar el cliente');

                // Restaurar botón
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.remove('hidden');
                    searchButton.querySelector('.loading-icon').classList.add('hidden');
                    searchButton.disabled = false;
                }

                return;
            }

            // Llamar directamente al método del componente Livewire
            @this.set('customerPhone', phone);
            @this.searchCustomerByPhone();

            // Verificar el resultado después de un breve retraso
            setTimeout(() => {
                // Restaurar botón
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.remove('hidden');
                    searchButton.querySelector('.loading-icon').classList.add('hidden');
                    searchButton.disabled = false;
                }

                // Verificar si el cliente existe en la base de datos
                const customerExists = @this.get('customerId');

                if (!customerExists) {
                    // Cliente no encontrado - mostrar mensaje manualmente
                    const notFoundMessage = document.getElementById('customer-not-found');
                    const notFoundPhone = document.getElementById('not-found-phone');

                    if (notFoundMessage && notFoundPhone) {
                        notFoundPhone.textContent = phone;
                        notFoundMessage.classList.remove('hidden');

                        // Habilitar el botón de guardar cliente
                        const saveButton = document.querySelector('button[wire\\:click="saveCustomer"]');
                        if (saveButton) {
                            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                            saveButton.disabled = false;
                        }

                        // Hacer que el campo de nombre reciba el foco
                        document.getElementById('customerName')?.focus();
                    }
                }
            }, 500);
        }

        // Funciones para mostrar modales
        function showCommandModal(url) {
            // Establecer la URL en el componente Livewire
            Livewire.dispatch('setCommandUrl', { url: url });

            // Mostrar el modal
            Livewire.dispatch('openCommandModal');
        }

        function showPreBillModal(url) {
            // Primero abrir el modal para que Alpine monte el iframe una sola vez
            Livewire.dispatch('openPreBillModal');

            // Luego establecer la URL (evita re-render adicional)
            Livewire.dispatch('setPreBillUrl', { url: url });
        }

        function abrirComanda() {
            // Obtener datos directamente del carrito visible
            const productos = [];
            document.querySelectorAll('[wire\\:key^="cart-item-"]').forEach(item => {
                const id = item.getAttribute('wire:key').replace('cart-item-', '');
                const name = item.querySelector('.cart-item-name-compact').textContent;
                const priceElement = item.querySelector('.cart-item-price-compact span');
                const price = priceElement ? parseFloat(priceElement.textContent.replace('S/ ', '')) : 0;
                const quantity = parseInt(item.querySelector('.quantity-value-compact').textContent);
                const subtotal = parseFloat(item.querySelector('.cart-item-subtotal-compact').textContent.replace('S/ ', ''));

                productos.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            });

            if (productos.length === 0) {
                alert('No hay productos en el carrito');
                return;
            }

            // Si hay una mesa seleccionada, asegurarse de que esté marcada como ocupada
            @if($tableId)
                // Verificar si la mesa está disponible y cambiarla a ocupada si es necesario
                @if($table && $table->status === 'available')
                    Livewire.dispatch('changeTableStatus', { tableId: {{ $tableId }}, status: 'occupied' });
                @endif
            @endif

            // Obtener el tipo de servicio actual directamente del DOM
            // Esto asegura que obtenemos el valor más actualizado, incluso si el usuario cambió el tipo de servicio
            let currentServiceType = '';

            // Verificar qué botón de tipo de servicio está activo (tiene la clase de fondo específica)
            if (document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-100') ||
                document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-900\\/50')) {
                currentServiceType = 'takeout';
            } else if (document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-100') ||
                       document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-900\\/50')) {
                currentServiceType = 'dine_in';
            } else if (document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-100') ||
                       document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-900\\/50')) {
                currentServiceType = 'delivery';
            } else {
                // Si no podemos determinar por el DOM, usar el valor de Livewire como respaldo
                currentServiceType = '{{ $serviceType }}'.trim();
            }

            console.log('Tipo de servicio detectado:', currentServiceType);

            // Si el tipo de servicio es "takeout" (Para Llevar), solicitar el nombre del cliente
            if (currentServiceType === 'takeout') {
                console.log('Solicitando nombre del cliente para pedido Para Llevar');
                // Mostrar un modal para solicitar el nombre del cliente
                Swal.fire({
                    title: 'Nombre del Cliente',
                    input: 'text',
                    inputLabel: 'Por favor, ingrese el nombre del cliente para el pedido Para Llevar',
                    inputPlaceholder: 'Nombre del cliente',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Debe ingresar un nombre para el cliente';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Nombre del cliente ingresado:', result.value);
                        // Continuar con la creación de la orden incluyendo el nombre del cliente
                        crearOrdenComanda(productos, result.value, currentServiceType);
                    }
                });
            } else {
                console.log('Tipo de servicio no es takeout, continuando sin solicitar nombre');
                // Para otros tipos de servicio, continuar sin solicitar nombre
                crearOrdenComanda(productos, null, currentServiceType);
            }
        }

        // Función auxiliar para crear la orden de comanda
        function crearOrdenComanda(productos, customerName = null, serviceType = null) {
            // Si no se proporciona un tipo de servicio, intentar detectarlo
            if (!serviceType) {
                // Verificar qué botón de tipo de servicio está activo
                if (document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-100') ||
                    document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-900\\/50')) {
                    serviceType = 'takeout';
                } else if (document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-100') ||
                           document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-900\\/50')) {
                    serviceType = 'dine_in';
                } else if (document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-100') ||
                           document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-900\\/50')) {
                    serviceType = 'delivery';
                } else {
                    // Si no podemos determinar por el DOM, usar el valor de Livewire como respaldo
                    serviceType = '{{ $serviceType }}'.trim();
                }
            }

            console.log('Tipo de servicio en crearOrdenComanda:', serviceType);

            // Crear el objeto de datos para la solicitud
            const requestData = {
                has_products: true,
                cart_items: productos,
                service_type: serviceType // Enviar el tipo de servicio detectado o proporcionado
            };

            // KISS: Obtener table_id directamente desde la URL o desde el DOM
            const urlParams = new URLSearchParams(window.location.search);
            const tableIdFromUrl = urlParams.get('table_id');
            const tableIdFromPath = window.location.pathname.match(/\/table\/(\d+)/);

            if (tableIdFromUrl) {
                requestData.table_id = parseInt(tableIdFromUrl);
                requestData.service_type = 'dine_in';
                console.log('Table ID desde URL:', tableIdFromUrl);
            } else if (tableIdFromPath) {
                requestData.table_id = parseInt(tableIdFromPath[1]);
                requestData.service_type = 'dine_in';
                console.log('Table ID desde path:', tableIdFromPath[1]);
            } else {
                // Buscar en el DOM si hay información de mesa
                const mesaInfo = document.querySelector('[data-table-id]');
                if (mesaInfo) {
                    requestData.table_id = parseInt(mesaInfo.getAttribute('data-table-id'));
                    requestData.service_type = 'dine_in';
                    console.log('Table ID desde DOM:', requestData.table_id);
                }
            }

            // Si se proporcionó un nombre de cliente, agregarlo a la solicitud
            if (customerName) {
                requestData.customer_name = customerName;
                console.log('Enviando nombre de cliente:', customerName);
            }

            console.log('Datos de la solicitud:', requestData);

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(requestData)
            })
            .then(procesarRespuesta)
            .then(orderId => {
                // Mostrar la comanda en un modal
                showCommandModal('{{ url("pos/command-pdf") }}/' + orderId);

                // ✅ Si es waiter, redirigir automáticamente al mapa de mesas después de un breve delay (TEMPORALMENTE DESHABILITADO)
                /*
                @if(Auth::user()->hasRole('waiter'))
                setTimeout(function() {
                    console.log('🔄 Redirigiendo waiter al mapa de mesas desde comanda...');
                    window.location.href = '{{ url("/admin/mapa-mesas") }}';
                }, 2000); // Dar tiempo para que se abra la ventana de impresión
                @endif
                */

                // NO vaciar el carrito después de generar la comanda
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error completo:', error);
            });
        }

        // Flag para evitar llamadas duplicadas al generar Pre-Cuenta
        let preBillEnProceso = false;

        function abrirPreCuenta() {
            if (preBillEnProceso) {
                console.warn('🔄 Pre-Cuenta ya en proceso, ignorando clic duplicado');
                return;
            }
            preBillEnProceso = true;
            const btn = document.getElementById('btn-precuenta');
            if (btn) btn.disabled = true;

            // Obtener datos directamente del carrito visible
            const productos = [];
            document.querySelectorAll('[wire\\:key^="cart-item-"]').forEach(item => {
                const id = item.getAttribute('wire:key').replace('cart-item-', '');
                const name = item.querySelector('.cart-item-name-compact').textContent;
                const priceElement = item.querySelector('.cart-item-price-compact span');
                const price = priceElement ? parseFloat(priceElement.textContent.replace('S/ ', '')) : 0;
                const quantity = parseInt(item.querySelector('.quantity-value-compact').textContent);
                const subtotal = parseFloat(item.querySelector('.cart-item-subtotal-compact').textContent.replace('S/ ', ''));

                productos.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            });

            if (productos.length === 0) {
                alert('No hay productos en el carrito');
                preBillEnProceso = false;
                if (btn) btn.disabled = false;
                return;
            }

            // Si hay una mesa seleccionada, asegurarse de que esté marcada como ocupada
            @if($tableId)
                // Verificar si la mesa está disponible y cambiarla a ocupada si es necesario
                @if($table && $table->status === 'available')
                    Livewire.dispatch('changeTableStatus', { tableId: {{ $tableId }}, status: 'occupied' });
                @endif
            @endif

            // Crear el objeto de datos para la solicitud
            const requestData = {
                has_products: true,
                cart_items: productos,
                service_type: 'dine_in' // Tipo de servicio por defecto para pre-cuenta
            };

            // KISS: Obtener table_id directamente desde la URL o desde el DOM
            const urlParams = new URLSearchParams(window.location.search);
            const tableIdFromUrl = urlParams.get('table_id');
            const tableIdFromPath = window.location.pathname.match(/\/table\/(\d+)/);
            const mesaInfo = document.querySelector('[data-table-id]');

            if (tableIdFromUrl) {
                requestData.table_id = parseInt(tableIdFromUrl);
                requestData.service_type = 'dine_in';
            } else if (tableIdFromPath) {
                requestData.table_id = parseInt(tableIdFromPath[1]);
                requestData.service_type = 'dine_in';
            } else if (mesaInfo) {
                requestData.table_id = parseInt(mesaInfo.getAttribute('data-table-id'));
                requestData.service_type = 'dine_in';
            }

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(requestData)
            })
            .then(procesarRespuesta)
            .then(orderId => {
                // Solo mostrar el modal, NO imprimir automáticamente
                showPreBillModal('{{ url("print/prebill") }}/' + orderId);
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error completo:', error);
            })
            .finally(() => {
                preBillEnProceso = false;
                if (btn) btn.disabled = false;
            });
        }

        function abrirModalTransferencia() {
            console.log('Abriendo modal de transferencia...');
            console.log('Mesa actual:', {{ $table ? $table->id : 'null' }});
            console.log('Productos en carrito:', {{ count($cart) }});

            // Verificar si hay productos en el carrito
            if ({{ count($cart) }} === 0) {
                alert('Transferir mesa: Esta función permite mover todos los productos de esta mesa a otra mesa disponible. Primero debes añadir productos al carrito.');
                return;
            }

            try {
                // Forzar la carga de mesas disponibles y abrir el modal directamente
                @this.openTransferModal();
                console.log('Modal abierto:', @this.showTransferModal);
            } catch (error) {
                console.error('Error al abrir modal de transferencia:', error);
                alert('Error al abrir el modal de transferencia: ' + (error.message || 'Error desconocido'));
            }
        }

        function cerrarModalTransferencia() {
            console.log('Cerrando modal de transferencia...');
            @this.showTransferModal = false;
            console.log('Modal cerrado');
        }

        function abrirModalDelivery() {
            console.log('Abriendo modal de delivery...');
            try {
                @this.openDeliveryModal();
                console.log('Modal de delivery abierto:', @this.showDeliveryModal);

                // Asegurarse de que el modal permanezca abierto
                setTimeout(() => {
                    if (!@this.showDeliveryModal) {
                        console.log('Forzando apertura del modal de delivery...');
                        @this.showDeliveryModal = true;
                    }
                }, 100);
            } catch (error) {
                console.error('Error al abrir modal de delivery:', error);
                alert('Error al abrir el modal de delivery: ' + (error.message || 'Error desconocido'));
            }
        }

        function transferirMesa(destinationTableId) {
            console.log('Transfiriendo a la mesa ID:', destinationTableId);

            try {
                // Mostrar mensaje de carga
                const mensaje = 'Transfiriendo productos a la mesa ' + destinationTableId + '...';
                console.log(mensaje);

                // Llamar al método del componente Livewire
                @this.transferTable(destinationTableId);

                // Cerrar el modal después de un breve retraso
                setTimeout(() => {
                    cerrarModalTransferencia();
                }, 500);
            } catch (error) {
                console.error('Error al transferir mesa:', error);
                alert('Error al transferir mesa: ' + error.message);
            }
        }

        function abrirFactura() {
            // Obtener datos directamente del carrito visible
            const productos = [];
            document.querySelectorAll('[wire\\:key^="cart-item-"]').forEach(item => {
                const id = item.getAttribute('wire:key').replace('cart-item-', '');
                const name = item.querySelector('.cart-item-name-compact').textContent;
                const priceElement = item.querySelector('.cart-item-price-compact span');
                const price = priceElement ? parseFloat(priceElement.textContent.replace('S/ ', '')) : 0;
                const quantity = parseInt(item.querySelector('.quantity-value-compact').textContent);
                const subtotal = parseFloat(item.querySelector('.cart-item-subtotal-compact').textContent.replace('S/ ', ''));

                productos.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            });

            if (productos.length === 0) {
                alert('No hay productos en el carrito');
                return;
            }

            // Si hay una mesa seleccionada, asegurarse de que esté marcada como ocupada
            @if($tableId)
                // Verificar si la mesa está disponible y cambiarla a ocupada si es necesario
                @if($table && $table->status === 'available')
                    Livewire.dispatch('changeTableStatus', { tableId: {{ $tableId }}, status: 'occupied' });
                @endif
            @endif

            // Detectar el tipo de servicio actual
            let currentServiceType = 'takeout'; // Por defecto para llevar

            // Verificar qué botón de tipo de servicio está activo
            if (document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-100') ||
                document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-900\\/50')) {
                currentServiceType = 'takeout';
            } else if (document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-100') ||
                       document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-900\\/50')) {
                currentServiceType = 'dine_in';
            } else if (document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-100') ||
                       document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-900\\/50')) {
                currentServiceType = 'delivery';
            } else {
                // Si no podemos determinar por el DOM, usar el valor de Livewire como respaldo
                currentServiceType = '{{ $serviceType }}'.trim();
            }

            console.log('Tipo de servicio detectado para facturación:', currentServiceType);

            // Crear el objeto de datos para la solicitud
            const requestData = {
                has_products: true,
                cart_items: productos,
                service_type: currentServiceType // Usar el tipo de servicio detectado
            };

            // KISS: Obtener table_id directamente desde la URL o desde el DOM
            const urlParams = new URLSearchParams(window.location.search);
            const tableIdFromUrl = urlParams.get('table_id');
            const tableIdFromPath = window.location.pathname.match(/\/table\/(\d+)/);
            const mesaInfo = document.querySelector('[data-table-id]');

            if (tableIdFromUrl) {
                requestData.table_id = parseInt(tableIdFromUrl);
                // Mantener el service_type detectado, no sobreescribir
            } else if (tableIdFromPath) {
                requestData.table_id = parseInt(tableIdFromPath[1]);
                // Mantener el service_type detectado, no sobreescribir
            } else if (mesaInfo) {
                requestData.table_id = parseInt(mesaInfo.getAttribute('data-table-id'));
                // Mantener el service_type detectado, no sobreescribir
            }

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(requestData)
            })
            .then(procesarRespuesta)
            .then(orderId => {
                // Abrir el formulario unificado de pago en una ventana nueva con tamaño optimizado
                const facturaWindow = window.open('{{ url("pos/unified") }}/' + orderId, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');

                // Cuando la factura se complete, vaciar el carrito
                if (facturaWindow) {
                    // Configurar el intervalo para chequear si la ventana se ha cerrado
                    const checkClosedInterval = setInterval(() => {
                        if (facturaWindow.closed) {
                            clearInterval(checkClosedInterval);
                            console.log('Ventana de factura cerrada - Vaciando carrito');
                            // Aquí sí vaciamos el carrito porque se ha emitido un comprobante
                            vaciarCarrito();
                        }
                    }, 1000);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error completo:', error);
            });
        }

        // Función para abrir el modal de transferencia
        function abrirModalTransferencia() {
            console.log('Abriendo modal de transferencia...');

            // Verificar si hay productos en el carrito
            if ({{ count($cart) }} === 0) {
                alert('No hay productos en el carrito para transferir. Primero debes añadir productos al carrito.');
                return;
            }

            try {
                // Llamar al método del componente Livewire para abrir el modal
                @this.openTransferModal();
                console.log('Modal abierto:', @this.showTransferModal);

                // Esperar a que el modal se abra completamente
                setTimeout(() => {
                    // Forzar la actualización de la UI después de que el modal esté visible
                    @this.refreshSelectedProducts();
                    console.log('Estado de selección actualizado');
                }, 300);
            } catch (error) {
                console.error('Error al abrir modal de transferencia:', error);
                alert('Error al abrir el modal de transferencia: ' + (error.message || 'Error desconocido'));
            }
        }

        // Función para cerrar el modal de transferencia
        function cerrarModalTransferencia() {
            @this.showTransferModal = false;
        }

        // Función para transferir mesa
        function transferirMesa(targetTableId) {
            @this.transferTable(targetTableId);
        }

        // Función para abrir el modal de delivery
        function abrirModalDelivery() {
            @this.showDeliveryModal = true;
        }

        // Ejecutar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar componentes si es necesario
        });

        // Función para ir a mesas
        function irAMesas() {
            // Mostrar mensaje de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Guardando carrito y actualizando mesa',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Llamar al método de Livewire para guardar el carrito
            Livewire.dispatch('guardarCarritoYRedirigir');
        }

        // Variables para el mapa
        let map;
        let marker;
        const defaultLat = -11.9865603;
        const defaultLng = -77.0679584;

        // Función para buscar dirección usando Nominatim
        function searchAddress() {
            const address = document.getElementById('searchAddress').value.trim();
            if (!address) {
                alert('Por favor ingresa una dirección para buscar');
                return;
            }

            // Mostrar indicador de carga
            const searchButton = document.querySelector('button[onclick="searchAddress();"]');
            const originalText = searchButton.innerHTML;
            searchButton.innerHTML = '<span class="inline-block mr-2 animate-spin">↻</span> Buscando...';
            searchButton.disabled = true;

            // Realizar la búsqueda con Nominatim
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    // Restaurar el botón
                    searchButton.innerHTML = originalText;
                    searchButton.disabled = false;

                    if (data && data.length > 0) {
                        // Encontró la ubicación
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);

                        // Actualizar el mapa
                        map.setView([lat, lon], 16);
                        marker.setLatLng([lat, lon]);

                        // Actualizar los campos de latitud y longitud
                        document.getElementById('latitude').value = lat.toFixed(7);
                        document.getElementById('longitude').value = lon.toFixed(7);
                    } else {
                        // No encontró la ubicación
                        alert('No se encontró la dirección. Por favor, ubica manualmente en el mapa.');
                    }
                })
                .catch(error => {
                    // Error en la búsqueda
                    console.error('Error al buscar la dirección:', error);
                    searchButton.innerHTML = originalText;
                    searchButton.disabled = false;
                    alert('Error al buscar la dirección. Por favor, ubica manualmente en el mapa.');
                });
        }

        // Función para inicializar el mapa
        function initMap() {
            // Crear el mapa centrado en la ubicación por defecto
            map = L.map('map').setView([defaultLat, defaultLng], 13);

            // Agregar capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Agregar marcador en la ubicación por defecto
            marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            // Actualizar campos de latitud y longitud al mover el marcador
            marker.on('dragend', function(event) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat.toFixed(7);
                document.getElementById('longitude').value = position.lng.toFixed(7);
            });

            // Inicializar campos de latitud y longitud
            document.getElementById('latitude').value = defaultLat;
            document.getElementById('longitude').value = defaultLng;

            // Permitir hacer clic en el mapa para mover el marcador
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(7);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(7);
            });
        }

        // Función para abrir el modal del mapa
        function openMapModal() {
            document.getElementById('mapModal').style.display = 'flex';
            // Inicializar el mapa después de que el modal sea visible
            setTimeout(function() {
                if (!map) {
                    initMap();
                } else {
                    // Si el mapa ya existe, actualizar su tamaño
                    map.invalidateSize();
                }
            }, 100);
        }

        // Función para cerrar el modal del mapa
        function closeMapModal() {
            document.getElementById('mapModal').style.display = 'none';
        }
    </script>

<!-- Modal del Mapa -->
<div id="mapModal" class="fixed inset-0 z-50 hidden bg-gray-600/75 dark:bg-gray-900/80" style="padding: 0 !important; display: none; align-items: center; justify-content: center;">
  <div style="width: 95vw !important; height: 95vh !important; max-width: none !important; max-height: none !important;" class="bg-white shadow-2xl dark:bg-gray-800 rounded-xl">
    <div class="flex items-center justify-between px-6 py-3 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        Ubicar Dirección en el Mapa
      </h3>
      <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="flex flex-col" style="height: calc(95vh - 64px) !important;">
      <!-- Campo de búsqueda en una barra fija en la parte superior -->
      <div class="px-6 py-2 border-b border-gray-200 dark:border-gray-700">
        <div class="flex space-x-2">
          <input
            type="text"
            id="searchAddress"
            placeholder="Ej: Av. Principal 123, Ciudad"
            class="w-full px-3 py-2 text-sm transition border border-gray-300 rounded-lg dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            onkeypress="if(event.key === 'Enter') { searchAddress(); return false; }"
          />
          <button
            type="button"
            onclick="searchAddress();"
            class="px-4 py-2 text-white transition-colors duration-200 bg-indigo-600 rounded-lg hover:bg-indigo-700 whitespace-nowrap"
          >
            Buscar
          </button>
        </div>
      </div>

      <!-- Área del mapa - Ocupa todo el espacio disponible -->
      <div class="flex-grow overflow-hidden" style="flex: 1 !important; min-height: 70vh !important;">
        <div id="map" class="w-full h-full"></div>
      </div>

      <!-- Controles en la parte inferior -->
      <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
        <!-- Campos de coordenadas -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="latitude" class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
              Latitud:
            </label>
            <input
              type="text"
              id="latitude"
              class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
              readonly
            />
          </div>
          <div>
            <label for="longitude" class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
              Longitud:
            </label>
            <input
              type="text"
              id="longitude"
              class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
              readonly
            />
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between space-x-4">
          <button
            type="button"
            onclick="closeMapModal()"
            class="w-1/2 px-6 py-2 font-medium text-gray-700 transition-colors duration-200 bg-gray-200 rounded-lg hover:bg-gray-300"
          >
            Volver
          </button>
          <button
            type="button"
            onclick="console.log('Ubicación confirmada'); closeMapModal();"
            class="w-1/2 px-6 py-2 font-medium text-white transition-colors duration-200 bg-green-600 rounded-lg hover:bg-green-700"
          >
            Confirmar Ubicación
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Escuchar el evento para abrir el modal de facturación después de la renderización
    window.addEventListener('open-invoice-modal-after-render', function() {
      // Esperar un momento para asegurar que el componente esté completamente renderizado
      setTimeout(function() {
        abrirFactura();
      }, 500);
    });
  });
</script>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('products-updated', () => {
            // Forzar recarga de imágenes
            document.querySelectorAll('img[wire\\:key^="product-image-"]').forEach(img => {
                const src = img.src;
                img.src = '';
                setTimeout(() => {
                    img.src = src;
                }, 100);
            });
        });
    });
</script>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product-images.css') }}">
@endpush
