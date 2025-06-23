/**
 * Sistema de modales para el POS
 * Este script maneja la creación y gestión de modales para comanda, pre-cuenta y factura
 */

// Variables globales
let activeModal = null;

/**
 * Cierra el modal activo
 */
function closeModal() {
    if (activeModal) {
        document.body.removeChild(activeModal);
        activeModal = null;
        document.removeEventListener('keydown', handleEscKey);
    }
}

/**
 * Maneja la tecla ESC para cerrar el modal
 * @param {KeyboardEvent} e - Evento de teclado
 */
function handleEscKey(e) {
    if (e.key === 'Escape' && activeModal) {
        closeModal();
    }
}

/**
 * Muestra un modal de comanda con diseño mejorado
 * @param {string} url - URL del PDF de la comanda
 * @returns {HTMLElement} - El modal creado
 */
function showCommandModal(url) {
    const printWindow = window.open(url, '_blank');

    if (!printWindow) {
        console.error('❌ No se pudo abrir la ventana de impresión');
        return;
    }

    printWindow.onload = function() {
        printWindow.print();
        setTimeout(function() {
            printWindow.close();
        }, 500);
    };
}

/**
 * Muestra un modal de factura con diseño mejorado
 * @param {string} url - URL del PDF de la factura
 * @returns {HTMLElement} - El modal creado
 */
function showInvoiceModal(url) {
    // Si hay un modal activo, cerrarlo primero
    if (activeModal) {
        closeModal();
    }

    // Crear el modal con un diseño especial para facturas
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50';
    modal.id = 'pos-modal-invoice';

    // Contenido del modal con diseño mejorado para facturas
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 bg-blue-600 dark:bg-blue-700">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Factura
                </h3>
                <button id="modal-close" class="text-white hover:text-gray-200 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 h-[70vh]">
                <iframe id="modal-iframe" src="${url}" class="w-full h-full border-0"></iframe>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 flex justify-end gap-3">
                <button
                    type="button"
                    id="modal-print"
                    class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </button>
                <button
                    type="button"
                    id="modal-close-btn"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cerrar
                </button>
            </div>
        </div>
    `;

    // Añadir el modal al body
    document.body.appendChild(modal);
    activeModal = modal;

    // Configurar eventos
    document.getElementById('modal-close').addEventListener('click', closeModal);
    document.getElementById('modal-close-btn').addEventListener('click', closeModal);

    // Añadir evento para cerrar al hacer clic fuera del modal
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Añadir evento para cerrar con ESC
    document.addEventListener('keydown', handleEscKey);

    return modal;
}

// Escuchar eventos de Livewire para el modal de pre-cuenta
document.addEventListener('livewire:initialized', () => {
    Livewire.on('modal-closed', () => {
        if (activeModal) {
            closeModal();
        }
    });

    Livewire.on('print-pre-bill', ({ content, styles }) => {
        const printWindow = window.open('', '_blank');

        if (!printWindow) {
            console.error('❌ No se pudo abrir la ventana de impresión');
            return;
        }

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Pre-Cuenta</title>
                <style>${styles}</style>
            </head>
            <body>
                ${content}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    };
                </script>
            </body>
            </html>
        `);
        printWindow.document.close();
    });
});

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
    const tableId = document.querySelector('[wire\\:model\\.live="selectedTableId"]') ? .value;

    // Crear la orden temporal
    fetch('/pos/create-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                table_id: tableId,
                products: productos
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al crear la orden');
            }
            return response.json();
        })
        .then(orderId => {
            // Mostrar la comanda en un modal
            const url = `/pos/command-pdf/${orderId}`;
            showCommandModal(url);
        })
        .catch(error => {
            alert('Error: ' + error.message);
            console.error('Error completo:', error);
        });
}
