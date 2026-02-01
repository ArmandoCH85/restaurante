@props([
    'heading' => null,
    'subheading' => null,
])

<style>
/* ===== LOGIN NEON FUTURISTA - CHIFA DEV ===== */
@import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.neon-login-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    font-family: 'Roboto', sans-serif;
}

/* Grid cibernético de fondo */
.cyber-grid {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        linear-gradient(rgba(0, 255, 255, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: grid-move 20s linear infinite;
    z-index: 1;
}

@keyframes grid-move {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
}

/* Efectos de partículas flotantes */
.particles {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 2;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #00ffff;
    border-radius: 50%;
    animation: float 6s infinite linear;
    box-shadow: 0 0 10px #00ffff, 0 0 20px #00ffff, 0 0 30px #00ffff;
}

@keyframes float {
    0% {
        transform: translateY(100vh) translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100vh) translateX(100px);
        opacity: 0;
    }
}

/* Caja de login con efecto neón */
.neon-login-box {
    position: relative;
    z-index: 10;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    border: 2px solid #00ffff;
    box-shadow: 
        0 0 5px #00ffff,
        0 0 10px #00ffff,
        0 0 20px #00ffff,
        0 0 40px #00ffff,
        inset 0 0 10px rgba(0, 255, 255, 0.1);
    animation: neon-pulse 2s ease-in-out infinite alternate;
}

@keyframes neon-pulse {
    0% {
        box-shadow: 
            0 0 5px #00ffff,
            0 0 10px #00ffff,
            0 0 20px #00ffff,
            0 0 40px #00ffff,
            inset 0 0 10px rgba(0, 255, 255, 0.1);
    }
    100% {
        box-shadow: 
            0 0 10px #00ffff,
            0 0 20px #00ffff,
            0 0 40px #00ffff,
            0 0 80px #00ffff,
            inset 0 0 20px rgba(0, 255, 255, 0.2);
    }
}

/* Logo y título con efecto neón */
.neon-header {
    text-align: center;
    margin-bottom: 30px;
}

.neon-logo {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(45deg, #00ffff, #ff00ff);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Orbitron', monospace;
    font-weight: 900;
    font-size: 24px;
    color: #000;
    text-shadow: 0 0 10px #fff;
    animation: logo-glow 1.5s ease-in-out infinite alternate;
}

@keyframes logo-glow {
    0% { 
        box-shadow: 0 0 20px #00ffff;
        transform: scale(1);
    }
    100% { 
        box-shadow: 0 0 40px #00ffff, 0 0 60px #ff00ff;
        transform: scale(1.05);
    }
}

.neon-title {
    font-family: 'Orbitron', monospace;
    font-size: 28px;
    font-weight: 700;
    color: #00ffff;
    text-align: center;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 3px;
    text-shadow: 
        0 0 5px #00ffff,
        0 0 10px #00ffff,
        0 0 20px #00ffff;
    animation: text-flicker 3s infinite;
}

@keyframes text-flicker {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

.neon-subtitle {
    font-size: 14px;
    color: #ff00ff;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 30px;
    text-shadow: 0 0 5px #ff00ff;
}

/* Estilos para los campos de entrada */
.filament-forms-input {
    background: rgba(0, 0, 0, 0.6) !important;
    border: 2px solid #00ffff !important;
    border-radius: 10px !important;
    color: #00ffff !important;
    font-family: 'Orbitron', monospace !important;
    font-size: 16px !important;
    padding: 15px !important;
    transition: all 0.3s ease !important;
}

.filament-forms-input:focus {
    outline: none !important;
    border-color: #ff00ff !important;
    box-shadow: 
        0 0 5px #ff00ff,
        0 0 10px #ff00ff,
        inset 0 0 5px rgba(255, 0, 255, 0.1) !important;
    background: rgba(0, 0, 0, 0.8) !important;
}

.filament-forms-input::placeholder {
    color: rgba(0, 255, 255, 0.5) !important;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Labels con estilo neón */
.filament-forms-field-wrapper-label {
    color: #00ffff !important;
    font-family: 'Orbitron', monospace !important;
    font-size: 12px !important;
    text-transform: uppercase !important;
    letter-spacing: 2px !important;
    margin-bottom: 8px !important;
    text-shadow: 0 0 5px #00ffff !important;
}

/* Botón de login con efecto neón */
.filament-button {
    background: linear-gradient(45deg, #00ffff, #0080ff) !important;
    border: none !important;
    border-radius: 10px !important;
    color: #000 !important;
    font-family: 'Orbitron', monospace !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    padding: 15px !important;
    text-transform: uppercase !important;
    letter-spacing: 2px !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    overflow: hidden !important;
    width: 100% !important;
    margin-top: 20px !important;
}

.filament-button:hover {
    background: linear-gradient(45deg, #ff00ff, #ff0080) !important;
    box-shadow: 
        0 0 10px #ff00ff,
        0 0 20px #ff00ff,
        0 0 30px #ff00ff !important;
    transform: translateY(-2px) !important;
}

.filament-button:active {
    transform: translateY(0) !important;
}

/* Animación de escaneo */
.scan-line {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #00ffff, transparent);
    animation: scan 2s linear infinite;
    z-index: 5;
}

@keyframes scan {
    0% { transform: translateY(-2px); }
    100% { transform: translateY(calc(100% + 2px)); }
}

/* Efecto de glitch para el título */
.glitch-effect {
    position: relative;
}

.glitch-effect::before,
.glitch-effect::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.glitch-effect::before {
    animation: glitch-1 2s infinite;
    color: #ff00ff;
    z-index: -1;
}

.glitch-effect::after {
    animation: glitch-2 2s infinite;
    color: #00ffff;
    z-index: -2;
}

@keyframes glitch-1 {
    0%, 100% { clip-path: inset(0 0 0 0); }
    20% { clip-path: inset(20% 0 30% 0); }
    40% { clip-path: inset(50% 0 20% 0); }
    60% { clip-path: inset(10% 0 60% 0); }
    80% { clip-path: inset(80% 0 5% 0); }
}

@keyframes glitch-2 {
    0%, 100% { clip-path: inset(0 0 0 0); }
    20% { clip-path: inset(60% 0 20% 0); }
    40% { clip-path: inset(20% 0 50% 0); }
    60% { clip-path: inset(40% 0 30% 0); }
    80% { clip-path: inset(10% 0 70% 0); }
}

/* Responsive */
@media (max-width: 480px) {
    .neon-login-box {
        margin: 20px;
        padding: 30px 20px;
    }
    
    .neon-title {
        font-size: 24px;
    }
    
    .neon-logo {
        width: 60px;
        height: 60px;
        font-size: 20px;
    }
}

/* Animación de entrada */
@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.neon-login-box {
    animation: fadeInUp 0.8s ease-out;
}
</style>

<div class="neon-login-container">
    <!-- Grid cibernético -->
    <div class="cyber-grid"></div>
    
    <!-- Partículas flotantes -->
    <div class="particles" id="particles"></div>
    
    <!-- Caja de login -->
    <div class="neon-login-box">
        <!-- Línea de escaneo -->
        <div class="scan-line"></div>
        
        <!-- Header -->
        <div class="neon-header">
            <div class="neon-logo">CD</div>
            <h1 class="neon-title glitch-effect" data-text="CHIFA DEV">CHIFA DEV</h1>
            <p class="neon-subtitle">SISTEMA DE ACCESO</p>
        </div>
        
        <!-- Formulario -->
        <div class="filament-form-container">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
// Generar partículas dinámicamente
document.addEventListener('DOMContentLoaded', function() {
    const particlesContainer = document.getElementById('particles');
    const numberOfParticles = 15;
    
    for (let i = 0; i < numberOfParticles; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (6 + Math.random() * 4) + 's';
        particlesContainer.appendChild(particle);
    }
    
    // Efecto de parpadeo aleatorio para el título
    const title = document.querySelector('.neon-title');
    setInterval(() => {
        if (Math.random() > 0.8) {
            title.style.opacity = '0.3';
            setTimeout(() => {
                title.style.opacity = '1';
            }, 100);
        }
    }, 2000);
});
</script>