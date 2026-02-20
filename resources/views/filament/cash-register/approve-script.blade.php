<script>
async function approveCashRegister(recordId, triggerElement = null) {
    if (!confirm('¿Está seguro de que desea aprobar esta caja registradora?')) {
        return;
    }

    const button = triggerElement
        || (typeof event !== 'undefined' ? event.currentTarget || event.target : null);
    const originalText = button ? button.innerHTML : null;

    try {
        if (button) {
            button.disabled = true;
            button.innerHTML = '<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Procesando…';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/admin/operaciones-caja/approve/${recordId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Error al aprobar la caja');
        }

        alert('Caja aprobada correctamente.');
        window.location.reload();
    } catch (error) {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }

        alert(`Error al aprobar: ${error.message}`);
    }
}
</script>
