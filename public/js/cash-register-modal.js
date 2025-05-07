/**
 * Sistema de modales para el POS y la caja registradora
 * Este script maneja la creación y gestión de modales para caja registradora
 */

// Asegurarse de que el script se ejecute solo una vez
if (typeof window.cashRegisterModalInitialized === 'undefined') {
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
            iframe.contentWindow.print();
        }
    }

    /**
     * Muestra un modal de caja registradora con diseño mejorado
     * @param {string} url - URL del contenido de la caja registradora
     * @returns {HTMLElement} - El modal creado
     */
    function showCashRegisterModal(url) {
        console.log('Mostrando modal para:', url);

        // Si hay un modal activo, cerrarlo primero
        if (activeModal) {
            closeModal();
        }

        // Crear el modal con un diseño especial para caja registradora
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50';
        modal.id = 'cash-register-modal';

        // Contenido del modal con diseño mejorado para caja registradora
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 bg-blue-600 dark:bg-blue-700">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Cierre de Caja
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
                        class="inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
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

    // Hacer la función disponible globalmente
    window.showCashRegisterModal = showCashRegisterModal;

    // Marcar el script como inicializado
    window.cashRegisterModalInitialized = true;

    console.log('Script de modal de caja registradora inicializado');
}
