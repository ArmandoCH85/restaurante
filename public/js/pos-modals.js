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
 * Imprime el contenido del iframe del modal
 */
function printModalContent() {
    const iframe = document.getElementById('modal-iframe');
    if (iframe && iframe.contentWindow) {
        // Usar el sistema unificado de impresión si está disponible
        if (window.printManager && iframe.src.includes('/invoices/print/')) {
            // Extraer el ID de la factura de la URL
            const matches = iframe.src.match(/\/invoices\/print\/([0-9]+)/);
            if (matches && matches[1]) {
                const invoiceId = matches[1];
                console.log('🔄 Delegando impresión al sistema unificado, ID:', invoiceId);
                window.printManager.printInvoice(invoiceId);
                return;
            }
        }

        // Fallback al método tradicional
        iframe.contentWindow.print();
    }
}

/**
 * Muestra un modal de comanda con diseño mejorado
 * @param {string} url - URL del PDF de la comanda
 * @returns {HTMLElement} - El modal creado
 */
function showCommandModal(url) {
    // Si hay un modal activo, cerrarlo primero
    if (activeModal) {
        closeModal();
    }
    
    // Crear el modal con un diseño especial para comandas
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50';
    modal.id = 'pos-modal-command';
    
    // Contenido del modal con diseño mejorado para comandas
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 bg-green-600 dark:bg-green-700">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Comanda
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
                    class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
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
    document.getElementById('modal-print').addEventListener('click', printModalContent);
    
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

/**
 * Muestra un modal de pre-cuenta con diseño mejorado
 * @param {string} url - URL del PDF de la pre-cuenta
 * @returns {HTMLElement} - El modal creado
 */
function showPreBillModal(url) {
    // Si hay un modal activo, cerrarlo primero
    if (activeModal) {
        closeModal();
    }
    
    // Crear el modal con un diseño especial para pre-cuentas
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50';
    modal.id = 'pos-modal-prebill';
    
    // Contenido del modal con diseño mejorado para pre-cuentas
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 bg-yellow-500 dark:bg-yellow-600">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Pre-Cuenta
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
                    class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400"
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
    document.getElementById('modal-print').addEventListener('click', printModalContent);
    
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

/**
 * Muestra un modal de factura con un diseño mejorado
 * @param {string} url - URL del formulario de factura
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-[90vw] h-[90vh] overflow-hidden">
            <div class="flex justify-between items-center px-4 py-2 bg-blue-600 dark:bg-blue-700">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Generación de Comprobante
                </h3>
                <button id="modal-close" class="text-white hover:text-gray-200 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-0 py-0 h-[calc(90vh-40px)]">
                <iframe id="modal-iframe" src="${url}" class="w-full h-full border-0" style="transform: scale(1.0); transform-origin: 0 0;"></iframe>
            </div>
        </div>
    `;
    
    // Añadir el modal al body
    document.body.appendChild(modal);
    activeModal = modal;
    
    // Configurar eventos
    document.getElementById('modal-close').addEventListener('click', closeModal);
    
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

// Configurar evento para escuchar mensajes de los iframes
window.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'invoice-completed') {
        // Cerrar el modal
        closeModal();
        
        // Disparar evento para que la página principal lo maneje
        window.dispatchEvent(new CustomEvent('invoice-completed'));
    }
});
