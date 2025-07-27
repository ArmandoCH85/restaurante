<x-filament-panels::page>
    @php
        // Forzar UTF-8 para caracteres especiales
        header('Content-Type: text/html; charset=UTF-8');
    @endphp
    <style>
        /* ========================================= */
        /* SISTEMA POS OPTIMIZADO - DISEÑO MODERNO */
        /* ========================================= */
        
        /* VARIABLES GLOBALES */
        :root {
            --pos-cart-width: 380px;
            --pos-sidebar-width: 200px;
            --pos-border-radius: 12px;
            --pos-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --pos-shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --pos-primary: #3b82f6;
            --pos-success: #10b981;
            --pos-warning: #f59e0b;
            --pos-danger: #ef4444;
            --pos-gray-50: #f8fafc;
            --pos-gray-100: #f1f5f9;
            --pos-gray-200: #e2e8f0;
        }
        
        /* RESET Y BASE */
        .pos-interface * {
            box-sizing: border-box;
        }
        
        /* LAYOUT PRINCIPAL - GRID OPTIMIZADO */
        .pos-main-container {
            display: grid;
            grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
            height: 100vh;
            overflow: hidden;
            gap: 0;
            background: var(--pos-gray-50);
        }
        
        /* SIDEBAR CATEGORÍAS - MEJORADO */
        .pos-categories {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-right: 1px solid var(--pos-gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .pos-categories-header {
            padding: 20px 16px 16px;
            border: 2px solid var(--pos-primary);
            border-radius: var(--pos-border-radius);
            background: white;
            margin: 12px;
        }
        
        .pos-categories-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }
        
        /* BOTONES DE CATEGORÍA MEJORADOS */
        .pos-category-btn {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 8px;
            text-align: left;
            border: 2px solid var(--pos-primary);
            border-radius: var(--pos-border-radius);
            background: white;
            color: var(--pos-primary);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .pos-category-btn:hover {
            background: #f3f4f6;
            border-color: var(--pos-primary);
            transform: translateY(-1px);
            box-shadow: var(--pos-shadow);
        }
        
        .pos-category-btn.active {
            background: var(--pos-primary);
            color: white;
            border: 2px solid var(--pos-primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        /* ÁREA DE PRODUCTOS OPTIMIZADA */
        .pos-products-area {
            background: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: calc(100vh - 120px);
        }
        
        .pos-search-bar {
            padding: 20px;
            background: white;
            border-bottom: 1px solid var(--pos-gray-200);
        }
        
        .pos-search-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            font-size: 16px;
            transition: all 0.2s ease;
            background: var(--pos-gray-50);
        }
        
        .pos-search-input:focus {
            outline: none;
            border-color: var(--pos-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .pos-products-grid {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        /* GRID DE PRODUCTOS RESPONSIVO */
        .pos-products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 8px;
            flex: 1;
            overflow-y: auto;
            padding-bottom: 20px;
        }
        
        /* CARDS DE PRODUCTOS MEJORADAS */
        .pos-product-card {
            background: white;
            border: 1px solid var(--pos-gray-200);
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
            position: relative;
            overflow: hidden;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .pos-product-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--pos-primary);
            background: #f8fafc;
        }
        
        .pos-product-card:active {
            transform: translateY(0);
        }
        
        .pos-product-image {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            margin: 0 auto 6px;
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
            margin-bottom: 4px;
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
        
        /* CARRITO OPTIMIZADO */
        .pos-cart {
            background: white;
            border-left: 1px solid var(--pos-gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .pos-cart-header {
            padding: 20px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-bottom: 1px solid var(--pos-gray-200);
        }
        
        .pos-cart-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .pos-cart-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }
        
        /* ACCIONES RÁPIDAS REORGANIZADAS */
        .pos-quick-actions {
            margin-top: 16px;
            padding: 16px;
            background: white;
            border-radius: var(--pos-border-radius);
            border: 1px solid var(--pos-gray-200);
        }
        
        .pos-quick-actions-title {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 12px;
            text-align: center;
        }
        
        .pos-quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        
        .pos-quick-action-btn {
            padding: 8px 6px;
            border: 1px solid var(--pos-gray-200);
            border-radius: 6px;
            background: white;
            color: #6b7280;
            font-size: 0;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            position: relative;
        }
        
        .pos-quick-action-btn:hover:not(:disabled) {
            background: var(--pos-gray-50);
            border-color: var(--pos-primary);
            color: var(--pos-primary);
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
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pos-quick-action-icon {
            width: 16px;
            height: 16px;
        }
        
        .pos-quick-action-btn span {
            display: none;
        }
        
        /* ITEMS DEL CARRITO MEJORADOS */
        .pos-cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            border: 2px solid var(--pos-primary);
            border-radius: var(--pos-border-radius);
            margin: 12px;
            background: white;
        }
        
        .pos-cart-item {
            background: var(--pos-gray-50);
            border: 1px solid var(--pos-gray-200);
            border-radius: var(--pos-border-radius);
            padding: 10px;
            margin-bottom: 8px;
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
        
        /* TOTALES DEL CARRITO */
        .pos-cart-totals {
            padding: 20px;
            background: white;
            border-top: 1px solid var(--pos-gray-200);
        }
        
        .pos-totals-container {
            background: var(--pos-gray-50);
            padding: 12px;
            border-radius: var(--pos-border-radius);
            margin-bottom: 12px;
        }
        
        .pos-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 13px;
        }
        
        .pos-total-row:last-child {
            margin-bottom: 0;
            padding-top: 6px;
            border-top: 1px solid var(--pos-gray-200);
            font-weight: 700;
            font-size: 14px;
        }
        
        .pos-total-row.final {
            color: var(--pos-success);
        }
        
        /* BOTONES DE ACCIÓN PRINCIPALES */
        .pos-action-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: var(--pos-border-radius);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
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
        
        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 1400px) {
            :root {
                --pos-cart-width: 350px;
                --pos-sidebar-width: 180px;
            }
            
            .pos-products-container {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
        }
        
        @media (max-width: 1200px) {
            :root {
                --pos-cart-width: 320px;
                --pos-sidebar-width: 160px;
            }
            
            .pos-quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 1024px) {
            .pos-main-container {
                grid-template-columns: 140px 1fr 300px;
            }
            
            .pos-category-btn {
                padding: 12px;
                font-size: 13px;
            }
            
            .pos-products-container {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
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

    <div class="pos-interface">
        <div class="pos-main-container">
            {{-- SIDEBAR IZQUIERDO: CATEGORÍAS --}}
            <div class="pos-categories">
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
                        wire:model.debounce.300ms="search"
                        placeholder="Buscar productos..."
                        class="pos-search-input"
                    />
                </div>

                {{-- GRID DE PRODUCTOS --}}
                <div class="pos-products-grid">
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

                    {{-- INFORMACIÓN ADICIONAL --}}
                    @if($products && $products->count() > 0)
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
                    <div class="pos-cart-title">
                        <span style="background: var(--pos-success); color: white; padding: 4px 12px; border-radius: 16px; font-size: 14px; font-weight: 600;">
                            {{ count($cartItems) }} items
                        </span>
                    </div>

                    {{-- CONTROLES PRINCIPALES --}}
                    <div class="pos-cart-actions" style="display: flex; align-items: end; gap: 8px;">
                        <div style="flex: 1;">
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
                            style="width: 32px; height: 32px; border: 1px solid #fca5a5; border-radius: 6px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;"
                            title="Limpiar carrito"
                            {{ !$canClearCart ? 'disabled' : '' }}
                            onmouseover="this.style.background='#fee2e2'"
                            onmouseout="this.style.background='#fef2f2'"
                        >
                            <x-heroicon-s-trash style="width: 16px; height: 16px;" />
                        </button>
                    </div>
                    
                    {{-- ACCIONES RÁPIDAS REORGANIZADAS --}}
                    <div class="pos-quick-actions">
                        <div class="pos-quick-actions-title">Acciones Rápidas</div>
                        <div class="pos-quick-actions-grid">
                            {{-- Mapa --}}
                            <button 
                                wire:click="mountAction('backToTableMap')"
                                class="pos-quick-action-btn btn-mapa"
                                {{ !($this->order && $this->order->table_id !== null) ? 'disabled' : '' }}
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