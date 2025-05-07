document.addEventListener('DOMContentLoaded', function() {
    // Función para aplicar estilos a las mesas
    function applyTableStyles() {
        console.log('Aplicando estilos a las mesas...');

        // Aplicar estilos a las mesas redondas
        const roundTables = document.querySelectorAll('.table-round');
        console.log('Mesas redondas encontradas:', roundTables.length);
        roundTables.forEach(table => {
            table.style.borderRadius = '50%';
            table.style.width = '60px';
            table.style.height = '60px';
            table.style.display = 'flex';
            table.style.alignItems = 'center';
            table.style.justifyContent = 'center';
            table.style.border = '2px solid black';
            table.style.backgroundColor = 'white';
            table.style.margin = '0 auto';
        });

        // Aplicar estilos a las mesas cuadradas
        const squareTables = document.querySelectorAll('.table-square');
        console.log('Mesas cuadradas encontradas:', squareTables.length);
        squareTables.forEach(table => {
            table.style.borderRadius = '4px';
            table.style.width = '60px';
            table.style.height = '60px';
            table.style.display = 'flex';
            table.style.alignItems = 'center';
            table.style.justifyContent = 'center';
            table.style.border = '2px solid black';
            table.style.backgroundColor = 'white';
            table.style.margin = '0 auto';
        });
    }

    // Aplicar estilos inmediatamente
    applyTableStyles();

    // Aplicar estilos después de un breve retraso para asegurarse de que el DOM esté completamente cargado
    setTimeout(applyTableStyles, 500);

    // Aplicar estilos cuando Livewire actualice el DOM
    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', (message, component) => {
            applyTableStyles();
        });
    });
});
