/**
 * Manejador de impresiones para sistema POS
 * Este script permite abrir ventanas de impresión para diferentes comprobantes
 */

// Función global para abrir ventana de impresión de facturas
function printInvoice(invoiceId) {
    if (!invoiceId) {
        console.error('Error: No se proporcionó ID de factura');
        return false;
    }

    console.log('Abriendo ventana de impresión para factura:', invoiceId);
    const printUrl = `/invoices/print/${invoiceId}`;
    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes');

    if (printWindow) {
        // Intentar imprimir automáticamente cuando la ventana esté cargada
        printWindow.addEventListener('load', function() {
            setTimeout(() => {
                printWindow.print();
            }, 1000); // Retraso para asegurar que los estilos se carguen
        });
        return true;
    } else {
        console.error('No se pudo abrir la ventana de impresión. Verifica el bloqueador de ventanas emergentes.');
        return false;
    }
}

// Inicializar listeners cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Escuchar eventos Livewire desde cualquier parte de la aplicación
    if (typeof Livewire !== 'undefined') {
        Livewire.on('open-print-window', function(data) {
            console.log('Evento open-print-window recibido con datos:', data);

            // Manejar diferentes formatos de datos
            let invoiceId = null;

            if (typeof data === 'object' && data !== null) {
                invoiceId = data.id || data.url || data;
            } else {
                invoiceId = data;
            }

            if (invoiceId) {
                printInvoice(invoiceId);
            } else {
                console.error('Datos de impresión inválidos:', data);
            }
        });
    } else {
        console.warn('Livewire no está disponible en esta página');
    }
});
