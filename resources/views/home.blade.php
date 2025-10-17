@extends('layouts.app')

@section('title', 'Software para restaurante con Facturación Electrónica - Waynasoft')

@section('description', 'Software de gestión para restaurantes con facturación electrónica SUNAT. Ventas, stock, logística, delivery y reportes en tiempo real. Aumenta tus ventas y controla tu negocio desde cualquier lugar.')

@push('styles')
<style>
    /* Base Styles */
    .btn-primary { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        padding: 12px 24px; 
        background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%); 
        color: white; 
        text-decoration: none; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 16px; 
        transition: all 0.3s; 
        border: none; 
        cursor: pointer; 
    }
    .btn-primary:hover { 
        background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%); 
        transform: translateY(-2px); 
        box-shadow: 0 8px 24px rgba(30, 136, 229, 0.3); 
    }
    .btn-secondary { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        padding: 12px 24px; 
        background: transparent; 
        color: #1e88e5; 
        text-decoration: none; 
        border: 2px solid #1e88e5; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 16px; 
        transition: all 0.3s; 
    }
    .btn-secondary:hover { 
        background: #1e88e5; 
        color: white; 
        transform: translateY(-2px); 
    }
    .section-title { 
        font-size: 36px; 
        text-align: center; 
        margin-bottom: 60px; 
        color: #333; 
        font-weight: bold; 
    }
    .highlight { 
        color: #1e88e5; 
        font-weight: bold; 
    }
    .logo-image { 
        width: 80px; 
        height: auto; 
    }

    /* Content Sections */
    .content-section { 
        padding: 80px 20px; 
        background-color: #f7fbff; /* suave azul muy claro */
    }
    .content-section:nth-child(even) { 
        background-color: #f2f6fc; /* variante ligeramente más intensa */
    }
    .content-container { 
        max-width: 1400px; 
        margin: 0 auto; 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 60px; 
        align-items: center; 
    }
    .content-container.reverse { 
        grid-template-columns: 1fr 1fr; 
    }
    .content-container.reverse .content-text { 
        order: 2; 
    }
    .content-container.reverse .content-image { 
        order: 1; 
    }
    .content-text h2 { 
        font-size: 36px; 
        margin-bottom: 20px; 
        color: #333; 
        font-weight: bold; 
        line-height: 1.2; 
    }
    .content-text p { 
        font-size: 16px; 
        color: #555; 
        margin-bottom: 20px; 
        line-height: 1.8; 
    }
    .content-text ul { 
        margin-bottom: 30px; 
    }
    .content-text li { 
        font-size: 16px; 
        color: #555; 
        margin-bottom: 8px; 
        line-height: 1.6; 
    }
    .content-image { 
        position: relative; 
        height: 400px; 
        overflow: hidden; 
        border-radius: 10px; 
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1); 
    }
    .content-image img { 
        width: 100%; 
        height: auto; 
        object-fit: cover; 
        border-radius: 10px; 
        display: block;
    }

    /* Hero Section */
    .hero { background: linear-gradient(135deg, #e3f2fd 0%, #f0f4ff 50%, #e8f5e9 100%); padding: 80px 20px; position: relative; overflow: hidden; }
    .hero::before { content: ''; position: absolute; top: -50%; left: -10%; width: 600px; height: 600px; background: rgba(255,255,255,0.3); border-radius: 50%; transform: rotate(45deg); }
    .hero-container { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 1; }
    .hero-content h1 { font-size: 56px; line-height: 1.2; margin-bottom: 30px; color: #333; }
    .hero-content h1 .blue { color: #1e88e5; }
    .hero-content p { font-size: 18px; color: #555; margin-bottom: 30px; line-height: 1.8; }
    .hero-buttons { display: flex; gap: 20px; margin-bottom: 40px; }
    .stats { display: flex; gap: 60px; margin-top: 40px; }
    .stat { display: flex; flex-direction: column; }
    .stat-number { font-size: 48px; font-weight: bold; color: #1e88e5; }
    .stat-label { font-size: 14px; color: #555; margin-top: 5px; }

    /* Hero Diagram */
    .hero-diagram { position: relative; height: 400px; display: flex; align-items: center; justify-content: center; }
    .diagram-circle { position: relative; width: 300px; height: 300px; border: 3px dashed #1e88e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .diagram-center { text-align: center; font-weight: bold; font-size: 18px; }
    .diagram-center .logo { margin-bottom: 5px; }
    .diagram-item { position: absolute; width: 100px; height: 100px; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px; text-align: center; }
    .diagram-item.franchises { background-color: #9c27b0; top: -30px; left: 50%; transform: translateX(-50%); }
    .diagram-item.point-of-sale { background-color: #2196f3; top: 50%; right: -30px; transform: translateY(-50%); }
    .diagram-item.stock { background-color: #4caf50; bottom: -30px; right: 20px; }
    .diagram-item.small { background-color: #757575; bottom: 20px; left: 20px; }
    .diagram-label { position: absolute; font-size: 14px; color: #888; font-weight: 600; transform: rotate(-45deg); }
    .label-logistics { top: 30%; left: -80px; }
    .label-services { top: 30%; right: -80px; }
    .label-reports { bottom: 30%; right: -80px; }
    .label-delivery { bottom: 30%; left: -80px; }

    /* Features Section */
    .features { padding: 80px 20px; background-color: #f7fbff; }
    .features-container { max-width: 1400px; margin: 0 auto; }
    .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-bottom: 60px; }
    .feature-card { text-align: center; }
    .feature-icon { width: 100px; height: 100px; margin: 0 auto 20px; background-color: #f5f5f5; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 40px; }
    .feature-card h3 { font-size: 16px; margin-bottom: 10px; color: #333; }
    .feature-card p { font-size: 14px; color: #666; }

    /* Benefits Section */
    .benefits { padding: 80px 20px; background-color: #f7fbff; }
    .benefits-container { max-width: 1400px; margin: 0 auto; }
    .benefits-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
    .benefit-item { text-align: center; }
    .benefit-icon { width: 120px; height: 120px; margin: 0 auto 20px; background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 50px; color: white; }
    .benefit-item h3 { font-size: 18px; margin-bottom: 15px; color: #333; }
    .benefit-item p { font-size: 14px; color: #555; line-height: 1.8; }

    /* Pricing Section */
    .pricing { padding: 80px 20px; background-color: #f2f6fc; }
    .pricing-container { max-width: 1400px; margin: 0 auto; }
    .pricing-header { text-align: center; margin-bottom: 60px; }
    .pricing-header h2 { font-size: 36px; margin-bottom: 20px; color: #333; }
    .pricing-header p { font-size: 16px; color: #555; }
    .pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
    .pricing-card { background-color: white; border-radius: 10px; padding: 40px 30px; border: 2px solid #e0e0e0; transition: all 0.3s; }
    .pricing-card:hover { border-color: #1e88e5; box-shadow: 0 8px 24px rgba(30, 136, 229, 0.15); }
    .pricing-card h3 { font-size: 24px; margin-bottom: 30px; color: #333; }
    .pricing-features { list-style: none; margin-bottom: 30px; }
    .pricing-features li { padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #555; }
    .pricing-features li:last-child { border-bottom: none; }
    .pricing-features li::before { content: '✓ '; color: #4caf50; font-weight: bold; margin-right: 10px; }
    .pricing-card .btn-primary { width: 100%; justify-content: center; }

    /* Responsive */
    @media (max-width: 1024px) {
        .hero-container { grid-template-columns: 1fr; }
        .features-grid { grid-template-columns: repeat(2, 1fr); }
        .benefits-grid { grid-template-columns: repeat(2, 1fr); }
        .pricing-grid { grid-template-columns: 1fr; }
        .hero-content h1 { font-size: 40px; }
    }
    @media (max-width: 768px) {
        .hero {
            padding: 48px 16px;
        }
        .hero-container {
            gap: 24px;
        }
        .hero-content h1 { font-size: 28px; }
        .hero-content p { font-size: 16px; }
        .hero-buttons { flex-direction: column; gap: 12px; margin-bottom: 24px; }

        .content-section { padding: 48px 16px; }
        .content-container { grid-template-columns: 1fr; gap: 24px; }
        .content-text h2 { font-size: 28px; }
        .content-text p, .content-text li { font-size: 15px; }

        /* Evitar imágenes demasiado grandes en móvil */
        .content-image { height: auto; overflow: visible; }
        .content-image img { width: 100%; height: auto; max-height: 60vh; object-fit: cover; }
        /* Igualar comportamiento de img1 e img3 al de img2 en móvil */
        .content-image--balanced img { object-fit: contain; max-height: 45vh; }

        .hero-diagram { height: 240px; }
        .diagram-circle { width: 180px; height: 180px; }
        .diagram-item { width: 68px; height: 68px; font-size: 10px; }
        .diagram-label { font-size: 12px; }
    }

    /* Extra pequeño (<=420px) */
    @media (max-width: 420px) {
        .hero-content h1 { font-size: 26px; }
        .content-text h2 { font-size: 26px; }
        .content-image img { max-height: 50vh; }
        .hero-diagram { height: 220px; }
        .diagram-circle { width: 160px; height: 160px; }
    }
    /* Imagen full-width previa a demo */
    .image-fullwidth-section { padding: 0; }
    .image-fullwidth-wrapper { width: 100%; background-color: #f2f6fc; }
    .image-fullwidth { width: 100%; height: auto; object-fit: contain; display: block; }

    @media (max-width: 768px) {
        .image-fullwidth { height: auto; }
    }
    /* Compensación del header sticky para anclas */
    #herramientas, #mas-info, #demo { scroll-margin-top: 120px; }

    @media (max-width: 768px) {
        #herramientas, #mas-info, #demo { scroll-margin-top: 96px; }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero" aria-labelledby="hero-title">
    <div class="hero-container">
        <div class="hero-content">
            <h1 id="hero-title">
                <span class="blue">Solución</span> para negocios<br>
                <span class="blue">Gastronómicos</span><br>
                ¡Todo en uno!
            </h1>
            <p>Tu aliado estratégico para llevar tu negocio al siguiente nivel.</p>
            <div class="hero-buttons">
                <a href="#demo" class="btn-primary">Solicita tu demo <span>→</span></a>
                <a href="#mas-info" class="btn-secondary">Más información <span>→</span></a>
            </div>

        </div>

        <div class="hero-diagram" aria-hidden="true">
            <div class="diagram-circle">
                <div class="diagram-center">
                    <div class="logo"><img src="{{ asset('images/logoWayna.svg') }}" alt="Waynasoft" class="logo-image"></div>
                </div>
                <div class="diagram-item franchises">🏪<br>FRANQUICIAS</div>
                <div class="diagram-item point-of-sale">💳<br>PUNTO DE VENTA</div>
                <div class="diagram-item stock">🍽️<br>MEDIANOS</div>
                <div class="diagram-item small">🏢<br>PEQUEÑOS</div>
                <div class="diagram-label label-logistics">Logística</div>
                <div class="diagram-label label-services">Control de Stock</div>
                <div class="diagram-label label-reports">Informes</div>
                <div class="diagram-label label-delivery">Entregas</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="herramientas">
    <div class="features-container">
        <h2 class="section-title">Solución 360º para cualquier tipo de negocio</h2>
        <div class="features-grid">
            <div class="feature-card"><div class="feature-icon">💰</div><h3>Control de caja</h3><p>Gestión completa de transacciones</p></div>
            <div class="feature-card"><div class="feature-icon">📦</div><h3>Gestión de productos</h3><p>Catálogo y control de inventario</p></div>
            <div class="feature-card"><div class="feature-icon">📊</div><h3>Control de insumos</h3><p>Seguimiento de materias primas</p></div>
            <div class="feature-card"><div class="feature-icon">📋</div><h3>Registro de recetas</h3><p>Documentación de preparaciones</p></div>
            <div class="feature-card"><div class="feature-icon">🏪</div><h3>Movimiento entre almacenes</h3><p>Transferencias de stock</p></div>
            <div class="feature-card"><div class="feature-icon">📄</div><h3>Facturación electrónica</h3><p>Cumplimiento normativo SUNAT</p></div>
            <div class="feature-card"><div class="feature-icon">📈</div><h3>Control de stock</h3><p>Monitoreo en tiempo real</p></div>
            <div class="feature-card"><div class="feature-icon">🚚</div><h3>Delivery</h3><p>Gestión de entregas</p></div>
        </div>
    </div>
</section>

<!-- Content Section 1 -->
<section class="content-section" id="mas-info">
    <div class="content-container">
        <div class="content-text">
            <h2>VENTA <span class="highlight">ONLINE</span></h2>
            <h3 style="font-size: 18px; color: #666; margin-bottom: 20px;">Integra tecnología a tu negocio.</h3>
            <p>Aumenta tus ventas a través de nuestros canales de venta en línea:</p>
            <ul style="margin-left: 20px; margin-bottom: 20px;">
                <li>✓ Menú online</li>
                <li>✓ Carta QR</li>
                <li>✓ Integraciones con App de delivery</li>
            </ul>
            <a href="#demo" class="btn-primary">MÁS INFORMACIÓN →</a>
        </div>
        <div class="content-image content-image--balanced"><img src="{{ asset('images/img1.png') }}" alt="Venta Online" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;"></div>
    </div>
</section>

<!-- Content Section 2 -->
<section class="content-section">
    <div class="content-container reverse">
        <div class="content-text">
            <h2>MEJORA TUS <span class="highlight">TIEMPOS</span></h2>
            <h3 style="font-size: 18px; color: #666; margin-bottom: 20px;">Realiza pedidos, emite comprobantes rápido y sencillo.</h3>
            <p>Reduce el tiempo de atención y ten el control de todos tus pedidos. Con ventas rápidas y avanzadas en un instante.</p>
            <a href="#demo" class="btn-primary">MÁS INFORMACIÓN →</a>
        </div>
        <div class="content-image"><img src="{{ asset('images/img2.png') }}" alt="Mejora de Tiempos" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;"></div>
    </div>
</section>

<!-- Content Section 3 -->
<section class="content-section">
    <div class="content-container">
        <div class="content-text">
            <h2>TODO EN <span class="highlight">TIEMPO REAL</span></h2>
            <h3 style="font-size: 18px; color: #666; margin-bottom: 20px;">Visualiza tus ventas, deliverys y stock.</h3>
            <p>Obtén información desde cualquier lugar y toma decisiones al instante.</p>
            <a href="#demo" class="btn-primary">MÁS INFORMACIÓN →</a>
        </div>
        <div class="content-image content-image--balanced"><img src="{{ asset('images/img3.png') }}" alt="Tiempo Real" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;"></div>
    </div>
</section>

<!-- Content Section 4 -->
<section class="content-section">
    <div class="content-container reverse">
        <div class="content-text">
            <h2>Software para restaurante con <span class="highlight">Facturación electrónica</span></h2>
            <p>Reduce procesos manuales y súmate a las nuevas normativas tributarias de la SUNAT.</p>
            <ul style="margin-left: 20px; margin-bottom: 20px;">
                <li>✓ Ahorra tiempo y dinero (recupera horas de trabajo)</li>
                <li>✓ Mejora el control de tu restaurante</li>
                <li>✓ Disminuye errores contables</li>
            </ul>
            <a href="#demo" class="btn-primary">MÁS INFORMACIÓN →</a>
        </div>
        <div class="content-image"><img src="{{ asset('images/img4.png') }}" alt="Facturación Electrónica" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;"></div>
    </div>
</section>



<!-- Imagen full-width previa a la demo -->
<section class="image-fullwidth-section" aria-hidden="true">
    <div class="image-fullwidth-wrapper">
        <img src="{{ asset('images/img6.png') }}" alt="Imagen representativa" class="image-fullwidth">
    </div>
</section>

<!-- Demo / Contacto -->
<section class="content-section" id="demo" aria-labelledby="demo-title">
    <div class="content-container">
        <div class="content-text">
            <h2 id="demo-title">Solicita una <span class="highlight">demo</span></h2>
            <p>Déjanos tus datos y un asesor te contactará.</p>
            <form id="demo-form" action="#" method="post" style="display:grid; gap:12px; max-width: 420px;">
                @csrf
                <label>
                    Nombre
                    <input type="text" name="name" required placeholder="Tu nombre" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </label>
                <label>
                    Email
                    <input type="email" name="email" required placeholder="tu@email.com" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </label>
                <label>
                    Teléfono
                    <input type="tel" name="phone" required placeholder="Tu teléfono" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </label>
                <label>
                    Mensaje
                    <textarea name="message" rows="3" placeholder="Cuéntanos sobre tu negocio" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;"></textarea>
                </label>
                <button type="submit" class="btn-primary" style="justify-content:center;">Enviar solicitud</button>
            </form>
        </div>
        <div class="content-image"><img src="{{ asset('images/img5.png') }}" alt="Demo del software" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;"></div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Demo form basic handler (prevent default for now)
    const demoForm = document.getElementById('demo-form');
    if (demoForm) {
        demoForm.addEventListener('submit', (e) => {
            e.preventDefault();
            alert('¡Gracias! Hemos recibido tu solicitud. Un asesor se pondrá en contacto contigo.');
            demoForm.reset();
        });
    }
</script>
@endpush