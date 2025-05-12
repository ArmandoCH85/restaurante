/**
 * Script para manejar las formas de mesas y sus estados
 */

// Función para aplicar estilos a las formas de mesas
function fixTableShapes() {
    // Seleccionar todas las mesas visuales
    const tables = document.querySelectorAll('.table-visual');
    
    // Aplicar estilos a cada mesa
    tables.forEach(table => {
        // Asegurarse de que los estilos se apliquen correctamente
        if (table.classList.contains('table-square')) {
            table.style.borderRadius = '4px';
        } else if (table.classList.contains('table-round')) {
            table.style.borderRadius = '50%';
        } else if (table.classList.contains('table-rectangular')) {
            table.style.width = '80px';
            table.style.height = '60px';
            table.style.borderRadius = '4px';
        } else if (table.classList.contains('table-oval')) {
            table.style.width = '80px';
            table.style.height = '60px';
            table.style.borderRadius = '50% / 70%';
        }
        
        // Aplicar estilos según el estado
        if (table.classList.contains('available')) {
            table.style.backgroundColor = '#d1fae5';
            table.style.borderColor = '#10b981';
        } else if (table.classList.contains('occupied')) {
            table.style.backgroundColor = '#fee2e2';
            table.style.borderColor = '#ef4444';
        } else if (table.classList.contains('reserved')) {
            table.style.backgroundColor = '#fef3c7';
            table.style.borderColor = '#f59e0b';
        } else if (table.classList.contains('inactive')) {
            table.style.backgroundColor = '#f3f4f6';
            table.style.borderColor = '#9ca3af';
            table.style.opacity = '0.7';
        }
    });
}

// Ejecutar la función cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar estilos inmediatamente
    fixTableShapes();
    
    // Aplicar estilos después de un tiempo para asegurarse de que todos los elementos estén cargados
    setTimeout(fixTableShapes, 100);
    setTimeout(fixTableShapes, 500);
    
    // Aplicar estilos cuando Livewire actualice el DOM
    if (typeof Livewire !== 'undefined') {
        document.addEventListener('livewire:load', fixTableShapes);
        document.addEventListener('livewire:update', fixTableShapes);
    }
    
    // Observar cambios en el DOM para aplicar estilos cuando se agreguen nuevos elementos
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            setTimeout(fixTableShapes, 100);
        });
        
        // Observar todo el documento para detectar cambios en el DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});
