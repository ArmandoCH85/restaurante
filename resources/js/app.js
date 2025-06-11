import './bootstrap';
import './print-handler';

// Configuración del sistema de logs
window.APP_DEBUG = true;

// Sistema centralizado de logs para diagnóstico de impresión
window.printLogger = {
    logLevel: 'debug', // 'debug', 'info', 'warn', 'error'

    // Método principal de registro
    log: function(level, message, data = {}) {
        const timestamp = new Date().toISOString();
        const entry = {
            timestamp,
            level,
            message,
            data,
            url: window.location.href,
            userAgent: navigator.userAgent.substring(0, 150) // Limitar tamaño
        };

        // Mostrar en consola con formato
        const styles = {
            'debug': 'color: #3498db;',
            'info': 'color: #2ecc71;',
            'warn': 'color: #f39c12; font-weight: bold;',
            'error': 'color: #e74c3c; font-weight: bold; background: rgba(231, 76, 60, 0.1); padding: 2px 4px; border-radius: 2px;'
        };

        console.log(`%c[PRINT-${level.toUpperCase()}] ${message}`, styles[level] || '', data);

        // Guardar en localStorage
        try {
            const logs = JSON.parse(localStorage.getItem('printSystemLogs') || '[]');
            logs.push(entry);
            // Mantener solo los últimos 100 registros
            if (logs.length > 100) logs.splice(0, logs.length - 100);
            localStorage.setItem('printSystemLogs', JSON.stringify(logs));
        } catch (e) {
            console.error('Error al guardar log:', e);
        }

        // Si es error, enviar a un endpoint de registro (para implementación futura)
        if (level === 'error' && window.APP_DEBUG) {
            // En el futuro se podría implementar un endpoint para registrar errores
            // fetch('/api/log-error', {...})
        }
    },

    // Métodos específicos por nivel
    debug: function(message, data) { this.log('debug', message, data); },
    info: function(message, data) { this.log('info', message, data); },
    warn: function(message, data) { this.log('warn', message, data); },
    error: function(message, data) { this.log('error', message, data); }
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    printLogger.info('Sistema de logs de impresión inicializado', {
        version: '1.0.0',
        timestamp: new Date().toISOString()
    });
});
