// Script para aplicar estilos dinámicos al navigation item de Apertura y Cierre de Caja
document.addEventListener('DOMContentLoaded', function() {
    // Función para aplicar estilos al elemento
    function applyCustomStyles() {
        // Buscar el elemento por texto
        const navItems = document.querySelectorAll('.fi-sidebar-nav-item');
        
        navItems.forEach(item => {
            const label = item.querySelector('.fi-sidebar-nav-item-label');
            const link = item.querySelector('a');
            
            // Verificar si contiene el texto "Apertura y Cierre de Caja" o la URL de cash-registers
            if ((label && label.textContent.includes('Apertura y Cierre de Caja')) || 
                (link && link.href && link.href.includes('cash-registers'))) {
                
                // Aplicar estilos al label
                if (label) {
                    label.style.backgroundColor = 'rgb(245, 158, 11)';
                    label.style.color = 'white';
                    label.style.padding = '0.25rem 0.5rem';
                    label.style.borderRadius = '0.375rem';
                    label.style.fontWeight = '600';
                    label.style.boxShadow = '0 1px 2px 0 rgb(0 0 0 / 0.05)';
                    label.style.display = 'inline-block';
                }
                
                // Agregar eventos hover
                item.addEventListener('mouseenter', function() {
                    if (label) {
                        label.style.backgroundColor = 'rgb(217, 119, 6)';
                    }
                });
                
                item.addEventListener('mouseleave', function() {
                    if (label) {
                        label.style.backgroundColor = item.classList.contains('fi-active') ? 
                            'rgb(180, 83, 9)' : 'rgb(245, 158, 11)';
                    }
                });
                
                // Verificar si está activo
                if (item.classList.contains('fi-active') && label) {
                    label.style.backgroundColor = 'rgb(180, 83, 9)';
                }
            }
        });
    }
    
    // Aplicar estilos inmediatamente
    applyCustomStyles();
    
    // Observar cambios en el DOM para aplicar estilos a elementos cargados dinámicamente
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                applyCustomStyles();
            }
        });
    });
    
    // Observar el sidebar
    const sidebar = document.querySelector('.fi-sidebar-nav');
    if (sidebar) {
        observer.observe(sidebar, {
            childList: true,
            subtree: true
        });
    }
});