<script>
async function approveCashRegister(recordId) {
    if (confirm('¿Está seguro de que desea aprobar esta caja registradora?')) {
        try {
            // Mostrar loading en el botón
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Procesando...';
            
            // Hacer petición AJAX
            const response = await fetch('/admin/operaciones-caja/approve/' + recordId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                alert('✅ Caja aprobada correctamente');
                
                // Recargar la página
                window.location.reload();
            } else {
                throw new Error(data.message || 'Error al aprobar la caja');
            }
            
        } catch (error) {
            // Restaurar botón y mostrar error
            button.disabled = false;
            button.innerHTML = originalText;
            
            alert('❌ Error al aprobar: ' + error.message);
        }
    }
}
</script>