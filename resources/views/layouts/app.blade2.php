<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Software para restaurante con Facturación Electrónica - Waynasoft')</title>
    <meta name="description" content="@yield('description', 'Software de gestión para restaurantes con facturación electrónica SUNAT. Ventas, stock, logística, delivery y reportes en tiempo real. Aumenta tus ventas y controla tu negocio desde cualquier lugar.')">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('og_title', 'Waynasoft - Software para restaurantes con Facturación Electrónica')">
    <meta property="og:description" content="@yield('og_description', 'Gestiona tu restaurante con ventas, stock, logística y delivery. Cumplimiento SUNAT y reportes en tiempo real.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og_url', 'https://www.restaurant.pe/')">
    <meta property="og:image" content="@yield('og_image', 'https://www.restaurant.pe/assets/og-image.jpg')">
    
    @stack('styles')
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; color: #333; line-height: 1.6; }

        /* Skip link */
        .skip-link { position: absolute; left: -999px; top: -999px; background:#1e88e5; color:#fff; padding:8px 12px; border-radius:4px; }
        .skip-link:focus { left: 10px; top: 10px; z-index: 1000; }

        /* Header */
        header { background-color: #f8f9fa; border-bottom: 1px solid #e0e0e0; position: sticky; top: 0; z-index: 100; }
        .header-top { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; padding: 12px 20px; font-size: 13px; color: #555; }
        .header-top-left { display: flex; gap: 30px; }
        .header-top-right { display: flex; gap: 12px; align-items: center; }
        .header-top-right a { color: #555; text-decoration: none; border-bottom: 2px solid transparent; transition: border-color 0.3s; }
        .header-top-right a:hover { border-bottom-color: #1e88e5; }
        .flag { width: 30px; height: 20px; background: linear-gradient(90deg, #ff0000 0%, #ff0000 33%, white 33%, white 66%, #ff0000 66%, #ff0000 100%); border-radius: 3px; }

        /* Main Navigation */
        nav { background-color: white; border-top: 1px solid #eee; border-bottom: 1px solid #e0e0e0; }
        .nav-container { max-width: 1400px; margin: 0 auto; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 10px; font-size: 24px; font-weight: bold; color: #333; text-decoration: none; }
        .logo-image { height: 120px; width: auto; }
        .logo-dot { color: #ff9800; font-size: 28px; }
        .logo-subtitle { font-size: 12px; color: #666; font-weight: bold; display: block; }
        .nav-links { display: flex; gap: 40px; align-items: center; list-style: none; }
        .nav-links a { color: #333; text-decoration: none; font-weight: 500; transition: color 0.3s; display: flex; align-items: center; gap: 5px; }
        .nav-links a:hover { color: #1e88e5; }
        .dropdown-arrow { font-size: 12px; }
        .demo-btn { background-color: #1e88e5; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: background-color 0.3s; display: flex; align-items: center; gap: 8px; }
        .demo-btn:hover { background-color: #1565c0; }

        /* Common Styles */
        .btn-primary { background-color: #1e88e5; color: white; padding: 15px 40px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; }
        .btn-primary:hover { background-color: #1565c0; }
        .btn-secondary { background-color: white; color: #333; padding: 15px 40px; border: 2px solid #333; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; }
        .btn-secondary:hover { background-color: #f5f5f5; }
        .section-title { font-size: 36px; font-weight: bold; margin-bottom: 50px; text-align: center; }
        .content-section { padding: 80px 20px; background-color: #fafafa; }
        .content-section:nth-child(even) { background-color: white; }
        .content-container { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .content-container.reverse { direction: ltr; }
        .content-container.reverse .content-text { order: 2; }
        .content-container.reverse .content-image { order: 1; }
        .content-text h2 { font-size: 32px; margin-bottom: 20px; color: #333; }
        .content-text h2 .highlight { color: #1e88e5; }
        .content-text p { font-size: 16px; color: #555; margin-bottom: 20px; line-height: 1.8; }
        .content-image { width: 100%; height: 300px; background: linear-gradient(135deg, #e3f2fd 0%, #f0f4ff 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #999; }

        /* Chat Bubble */
        .chat-bubble { position: fixed; bottom: 30px; left: 30px; background-color: #4caf50; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 50; border: none; }
        .chat-message { position: fixed; bottom: 100px; left: 30px; background-color: white; color: #333; padding: 15px 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); max-width: 260px; font-size: 14px; z-index: 50; display: none; }
        .chat-message::after { content: ''; position: absolute; bottom: -10px; left: 20px; width: 0; height: 0; border-left: 10px solid transparent; border-right: 0 solid transparent; border-top: 10px solid white; }
        .close-chat { position: absolute; top: 5px; right: 8px; cursor: pointer; font-size: 18px; color: #999; background:none; border:none; }

        /* Footer */
        footer { background-color: #333; color: white; padding: 60px 20px 30px; }
        .footer-container { max-width: 1400px; margin: 0 auto; }
        .footer-content { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; margin-bottom: 40px; }
        .footer-section h4 { font-size: 16px; margin-bottom: 20px; color: white; }
        .footer-section ul { list-style: none; }
        .footer-section ul li { margin-bottom: 10px; }
        .footer-section ul li a { color: #ccc; text-decoration: none; font-size: 14px; transition: color 0.3s; }
        .footer-section ul li a:hover { color: #1e88e5; }
        .footer-bottom { border-top: 1px solid #555; padding-top: 30px; text-align: center; font-size: 14px; color: #bbb; }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-container { grid-template-columns: 1fr; }
            .nav-links { gap: 20px; }
        }
        @media (max-width: 768px) {
            .header-top { flex-direction: column; gap: 10px; }
            .nav-container { flex-direction: column; gap: 15px; }
            .nav-links { flex-direction: column; gap: 10px; width: 100%; }
            .footer-content { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    
    @stack('head-scripts')
</head>
<body>
    <a class="skip-link" href="#inicio">Saltar al contenido</a>
    
    <!-- HEADER CONSOLIDADO -->
    <header>
        <div class="header-top">
            <div class="header-top-left">
                <span>¿Ofreces servicios, productos o asesorías para restaurantes?</span>
            </div>
            <div class="header-top-right">
                <a href="#partner">Hazte partner</a>
                <span>Estamos en</span>
                <div class="flag" aria-label="Bandera de Perú" role="img"></div>
                <span>¡Perú!</span>
            </div>
        </div>

        <nav aria-label="Navegación principal">
            <div class="nav-container">
                <a href="#inicio" class="logo">
                    <img src="{{ asset('image/logoWayna.svg') }}" alt="Waynasoft" class="logo-image">
                    <span class="logo-subtitle">Software de gestión para restaurantes</span>
                </a>
                <ul class="nav-links">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#herramientas">Herramientas <span class="dropdown-arrow">▼</span></a></li>
                    <li><a href="#demo" class="demo-btn">Solicita Demo <span>→</span></a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <main id="inicio">
        @yield('content')
    </main>
    
    <!-- FOOTER CONSOLIDADO -->
    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Producto</h4>
                    <ul>
                        <li><a href="#herramientas">Características</a></li>
                        <li><a href="#blog" aria-disabled="true" tabindex="-1">Blog (próximamente)</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Empresa</h4>
                    <ul>
                        <li><a href="#about" aria-disabled="true" tabindex="-1">Acerca de nosotros (próx.)</a></li>
                        <li><a href="#demo">Contacto</a></li>
                        <li><a href="#careers" aria-disabled="true" tabindex="-1">Carreras (próx.)</a></li>
                        <li><a href="#press" aria-disabled="true" tabindex="-1">Prensa (próx.)</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#privacy" aria-disabled="true" tabindex="-1">Privacidad (próx.)</a></li>
                        <li><a href="#terms" aria-disabled="true" tabindex="-1">Términos de servicio (próx.)</a></li>
                        <li><a href="#cookies" aria-disabled="true" tabindex="-1">Política de cookies (próx.)</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Síguenos</h4>
                    <ul>
                        <li><a href="#facebook" aria-disabled="true" tabindex="-1">Facebook (próx.)</a></li>
                        <li><a href="#instagram" aria-disabled="true" tabindex="-1">Instagram (próx.)</a></li>
                        <li><a href="#linkedin" aria-disabled="true" tabindex="-1">LinkedIn (próx.)</a></li>
                        <li><a href="#twitter" aria-disabled="true" tabindex="-1">Twitter (próx.)</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Restaurant.pe - Software de gestión para restaurantes. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    
    <!-- CHAT CONSOLIDADO -->
    <button class="chat-bubble" aria-expanded="false" aria-controls="chat-message" aria-label="Abrir chat">💬</button>
    <div id="chat-message" class="chat-message" role="dialog" aria-live="polite">
        <button class="close-chat" aria-label="Cerrar chat">✕</button>
        <strong>¿Buscas incrementar tus ingresos?</strong><br>
        <span style="color: #1e88e5;">¡Contáctanos!</span>
    </div>
    
    @stack('scripts')
    
    <script>
        // Chat bubble functionality (accesible)
        const chatBubble = document.querySelector('.chat-bubble');
        const chatMessage = document.getElementById('chat-message');
        const closeChat = document.querySelector('.close-chat');

        function setChatVisibility(show) {
            chatMessage.style.display = show ? 'block' : 'none';
            chatBubble.setAttribute('aria-expanded', String(show));
        }
        setChatVisibility(false);

        chatBubble.addEventListener('click', () => {
            const isShown = chatMessage.style.display !== 'none';
            setChatVisibility(!isShown);
        });
        chatBubble.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const isShown = chatMessage.style.display !== 'none';
                setChatVisibility(!isShown);
            }
        });
        closeChat.addEventListener('click', (e) => {
            e.stopPropagation();
            setChatVisibility(false);
        });

        // Smooth scroll for internal navigation links only
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                const target = href ? document.querySelector(href) : null;
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>