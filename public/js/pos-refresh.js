// Script para forzar la recarga de la página y limpiar la caché
document.addEventListener('DOMContentLoaded', function() {
    // Crear una marca de tiempo para la versión actual
    const currentVersion = '2.0.3'; // Versión actualizada para mejorar la apertura del formulario de facturación

    // Verificar si ya se ha recargado la página con esta versión
    if (sessionStorage.getItem('posPageVersion') !== currentVersion) {
        // Marcar que la página se ha recargado con esta versión
        sessionStorage.setItem('posPageVersion', currentVersion);

        // Forzar la recarga de la página sin caché
        window.location.reload(true);
    }

    // Limpiar todos los estilos en caché
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
        if (link.href.includes('pos-cart-improvements.css')) {
            const newHref = link.href.split('?')[0] + '?v=' + new Date().getTime();
            link.href = newHref;
        }
    });
});
