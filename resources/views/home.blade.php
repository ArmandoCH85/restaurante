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
    .hero { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 140px 20px 100px; position: relative; overflow: hidden; }
    .hero::before { content: ''; position: absolute; top: -50%; left: -10%; width: 600px; height: 600px; background: rgba(255,255,255,0.3); border-radius: 50%; transform: rotate(45deg); }
    .hero-container { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 100px; align-items: center; position: relative; z-index: 1; }
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

    /* Features Section - Mejorado */
    .features { padding: 80px 20px; background-color: #f7fbff; }
    .features-container { max-width: 1400px; margin: 0 auto; }
    .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-bottom: 60px; }
    
    @media (max-width: 1024px) {
        .features-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
    }
    @media (max-width: 768px) {
        .features { padding: 60px 16px; }
        .features-grid { grid-template-columns: 1fr; gap: 20px; }
    }

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

    @media (max-width: 768px) {
        .hero-container { grid-template-columns: 1fr; gap: 60px; }
        .hero-content h1 { font-size: 36px; }
        .hero-content p { font-size: 16px; line-height: 1.7; }
        .hero-buttons { flex-direction: column; gap: 12px; margin-bottom: 60px; }
        .hero-buttons a { width: 100%; justify-content: center; }
        .hero-diagram { height: 400px; }
        .stats { flex-direction: column; gap: 24px; }
        .content-section { padding: 48px 16px; }
        .content-container { grid-template-columns: 1fr; gap: 24px; }
        .content-text h2 { font-size: 28px; }
        .content-text p, .content-text li { font-size: 15px; }
        .content-image { height: auto; overflow: visible; }
        .content-image img { width: 100%; height: auto; max-height: 60vh; object-fit: cover; }
        .content-image--balanced img { object-fit: contain; max-height: 45vh; }
        .features-grid { grid-template-columns: repeat(2, 1fr); }
        .benefits-grid { grid-template-columns: repeat(2, 1fr); }
        .pricing-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .hero { padding: 60px 16px 48px; }
        .hero-content h1 { font-size: 28px; }
        .hero-content p { font-size: 15px; }
        .content-text h2 { font-size: 26px; }
        .content-image img { max-height: 50vh; }
        .image-fullwidth { height: auto; }
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

    /* ===== RESPONSIVE PARA HERO SECTION NUEVO ===== */
    @media (max-width: 1024px) {
        /* Hero principal */
        section[style*="padding: 160px"] {
            padding: 120px 20px 80px !important;
        }
    }

    @media (max-width: 768px) {
        /* Hero section */
        section[style*="padding: 160px"] {
            padding: 80px 16px 60px !important;
        }
        section[style*="padding: 160px"] h1 {
            font-size: 42px !important;
            margin-bottom: 16px !important;
        }
        section[style*="padding: 160px"] > div > div:nth-child(2) p {
            font-size: 16px !important;
            margin-bottom: 32px !important;
        }
        /* Grid de imagen y beneficios */
        div[style*="display: grid; grid-template-columns: 1fr 1fr; gap: 60px"] {
            grid-template-columns: 1fr !important;
            gap: 40px !important;
        }
        /* Dashboard mockup altura */
        div[style*="position: relative; height: 500px"] {
            height: 300px !important;
        }
        /* Heading de beneficios */
        div[style*="display: grid; grid-template-columns: 1fr 1fr; gap: 60px"] h2 {
            font-size: 36px !important;
            margin-bottom: 24px !important;
        }
        /* Stats grid */
        div[style*="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; padding: 60px"] {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
            padding: 40px 20px !important;
        }
        div[style*="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; padding: 60px"] > div {
            font-size: 14px !important;
        }
        div[style*="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; padding: 60px"] > div > div:first-child {
            font-size: 40px !important;
        }
        /* Badges */
        div[style*="display: flex; justify-content: center; align-items: center; gap: 24px"] {
            gap: 12px !important;
        }
        div[style*="display: flex; justify-content: center; align-items: center; gap: 24px"] > div {
            font-size: 11px !important;
            padding: 8px 12px !important;
        }
        /* CTA buttons */
        div[style*="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap"] {
            flex-direction: column !important;
            gap: 12px !important;
        }
        div[style*="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap"] a {
            width: 100% !important;
            padding: 14px 24px !important;
            font-size: 14px !important;
        }
        /* Beneficios flex */
        div[style*="display: flex; flex-direction: column; gap: 20px"] {
            gap: 16px !important;
        }
    }

    @media (max-width: 480px) {
        section[style*="padding: 160px"] {
            padding: 60px 16px 40px !important;
        }
        section[style*="padding: 160px"] h1 {
            font-size: 32px !important;
        }
        section[style*="padding: 160px"] > div > div:nth-child(2) p {
            font-size: 15px !important;
        }
        div[style*="position: relative; height: 500px"] {
            height: 250px !important;
        }
        div[style*="display: grid; grid-template-columns: 1fr 1fr; gap: 60px"] h2 {
            font-size: 28px !important;
        }
        div[style*="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; padding: 60px"] {
            grid-template-columns: 1fr !important;
        }
        div[style*="display: flex; justify-content: center; align-items: center; gap: 24px"] {
            flex-direction: column !important;
        }
    }

    /* ===== RESPONSIVE PARA FEATURES SECTION ===== */
    @media (max-width: 768px) {
        section[style*="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 120px"] {
            padding: 80px 16px !important;
        }
        section[style*="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 120px"] > div > div:first-child {
            margin-bottom: 60px !important;
        }
        section[style*="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 120px"] h2 {
            font-size: 36px !important;
        }
        section[style*="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 120px"] p {
            font-size: 16px !important;
        }
        .features-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* ===== RESPONSIVE PARA DEMO SECTION ===== */
    @media (max-width: 1024px) {
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] {
            padding: 100px 20px !important;
        }
    }

    @media (max-width: 768px) {
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] {
            padding: 80px 16px !important;
        }
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] > div > div {
            grid-template-columns: 1fr !important;
            gap: 60px !important;
        }
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] h2 {
            font-size: 40px !important;
            margin-bottom: 20px !important;
        }
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] > div > div > div:first-child p {
            font-size: 16px !important;
            margin-bottom: 32px !important;
        }
        /* Formulario */
        div[style*="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px)"] {
            padding: 40px 24px !important;
        }
        div[style*="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px)"] h3 {
            font-size: 24px !important;
        }
        div[style*="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px)"] input,
        div[style*="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px)"] button {
            font-size: 14px !important;
        }
    }

    @media (max-width: 480px) {
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] {
            padding: 60px 16px !important;
        }
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] h2 {
            font-size: 32px !important;
        }
        section[style*="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%)"] > div > div > div:first-child {
            margin-bottom: 40px !important;
        }
        div[style*="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px)"] {
            padding: 32px 16px !important;
        }
        /* Beneficios */
        div[style*="display: flex; gap: 16px; align-items: flex-start"] {
            gap: 12px !important;
        }
        div[style*="display: flex; gap: 16px; align-items: flex-start"] > div:first-child {
            min-width: 40px !important;
            width: 40px !important;
            height: 40px !important;
        }
    }

    /* ===== RESPONSIVE PARA CONTENT SECTIONS (Venta Online, Velocidad, etc) ===== */
    @media (max-width: 768px) {
        /* Content sections con padding y layout */
        section[style*="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%)"],
        section[style*="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%)"],
        section[style*="background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%)"],
        section[style*="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)"] {
            padding: 60px 16px !important;
        }
        
        /* Content container a 1 columna */
        div[style*="display: grid; grid-template-columns: 1fr 1fr; gap: 60px"] {
            grid-template-columns: 1fr !important;
            gap: 40px !important;
        }
        
        /* Headings de content sections */
        section[style*="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%)"] h2,
        section[style*="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%)"] h2,
        section[style*="background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%)"] h2,
        section[style*="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)"] h2 {
            font-size: 36px !important;
            margin-bottom: 16px !important;
        }
        
        /* Párrafos de content */
        section[style*="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%)"] p,
        section[style*="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%)"] p,
        section[style*="background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%)"] p,
        section[style*="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)"] p {
            font-size: 15px !important;
        }
        
        /* Content image altura */
        div[style*="position: relative; min-height: 450px"] {
            min-height: 300px !important;
        }
        
        /* Features grid en content */
        div[style*="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px"] {
            grid-template-columns: 1fr !important;
        }
        
        /* Padding de content-text */
        div[style*="padding-right: 40px"],
        div[style*="padding-left: 40px"] {
            padding-right: 0 !important;
            padding-left: 0 !important;
        }
        
        /* Badges en content */
        div[style*="display: inline-block; padding: 8px 16px; background: rgba"] {
            margin-bottom: 16px !important;
        }
        
        /* Numbered badges */
        div[style*="display: flex; align-items: flex-start; gap: 16px"] {
            gap: 12px !important;
        }
    }

    @media (max-width: 480px) {
        section[style*="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%)"],
        section[style*="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%)"],
        section[style*="background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%)"],
        section[style*="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)"] {
            padding: 40px 16px !important;
        }
        
        section[style*="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%)"] h2,
        section[style*="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%)"] h2,
        section[style*="background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%)"] h2,
        section[style*="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)"] h2 {
            font-size: 28px !important;
        }
        
        div[style*="position: relative; min-height: 450px"] {
            min-height: 250px !important;
        }
    }

    /* ===== RESPONSIVE PARA TESTIMONIOS Y PASOS ===== */
    @media (max-width: 768px) {
        /* Testimonios section */
        section[style*="padding: 120px 20px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"] {
            padding: 80px 16px !important;
        }
        section[style*="padding: 120px 20px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"] > div > div:first-child {
            margin-bottom: 60px !important;
        }
        section[style*="padding: 120px 20px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"] h2 {
            font-size: 36px !important;
        }
        
        /* Testimonios grid */
        div[style*="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px"] {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        
        /* Testimonios cards */
        div[style*="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0"] {
            padding: 24px !important;
        }
        
        /* Texto testimonios */
        div[style*="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0"] p {
            font-size: 14px !important;
        }
    }

    @media (max-width: 480px) {
        section[style*="padding: 120px 20px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"] {
            padding: 60px 16px !important;
        }
        section[style*="padding: 120px 20px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"] h2 {
            font-size: 28px !important;
        }
        div[style*="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0"] {
            padding: 20px !important;
        }
    }

    /* ===== RESPONSIVE PARA SECCIÓN DE PASOS (Comienza en 4 Pasos) ===== */
    @media (max-width: 768px) {
        /* Steps section */
        section[style*="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px"] {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }
        
        /* Steps header */
        div[style*="text-align: center; margin-bottom: 100px"] h2 {
            font-size: 36px !important;
        }
        div[style*="text-align: center; margin-bottom: 100px"] p {
            font-size: 16px !important;
        }
        
        /* Step circles */
        div[style*="width: 120px; height: 120px; background: linear-gradient"] {
            width: 100px !important;
            height: 100px !important;
            font-size: 40px !important;
        }
        
        /* CTA section */
        div[style*="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 20px; padding: 60px 40px"] {
            padding: 40px 24px !important;
        }
    }

    @media (max-width: 480px) {
        section[style*="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px"] {
            grid-template-columns: 1fr !important;
        }
        div[style*="text-align: center; margin-bottom: 100px"] h2 {
            font-size: 28px !important;
        }
        div[style*="width: 120px; height: 120px; background: linear-gradient"] {
            width: 90px !important;
            height: 90px !important;
            font-size: 36px !important;
        }
        div[style*="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 20px; padding: 60px 40px"] {
            padding: 32px 16px !important;
        }
        div[style*="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 20px; padding: 60px 40px"] h3 {
            font-size: 22px !important;
        }
    }

    /* ===== RESPONSIVE PARA BENEFICIOS POR ROL ===== */
    @media (max-width: 768px) {
        /* Roles section */
        div[style*="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px"] {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
    }

    @media (max-width: 480px) {
        div[style*="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px"] {
            grid-template-columns: 1fr !important;
        }
    }

    /* Animaciones para Hero Section */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes barGrow {
        from {
            transform: scaleY(0);
            opacity: 0;
        }
        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }
</style>
@endpush

@section('content')
<!-- HERO SECTION ULTRA PROFESIONAL - Diseño SaaS 2025 Premium -->
<section style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #ffffff 100%); padding: 160px 20px 120px; position: relative; overflow: hidden;">
    <!-- Elementos decorativos de fondo mejorados -->
    <div style="position: absolute; top: -40%; right: -10%; width: 700px; height: 700px; background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none; animation: float 6s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -20%; left: -5%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.06) 0%, transparent 70%); border-radius: 50%; pointer-events: none; animation: float 8s ease-in-out infinite reverse;"></div>
    
    <!-- Nuevo: Elemento de video/simulación de fondo -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.03; background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%233b82f6" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>'); pointer-events: none;"></div>
    
    <div style="max-width: 1400px; margin: 0 auto; position: relative; z-index: 1;">
        <!-- Micro-copy de credibilidad con badges premium - MEJORADO -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 24px; margin-bottom: 48px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(16, 185, 129, 0.1); border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.2); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(16, 185, 129, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#10b981"><path d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span style="font-size: 12px; font-weight: 700; color: #10b981;">CERTIFICADO SUNAT</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; border: 1px solid rgba(59, 130, 246, 0.2); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(59, 130, 246, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#3b82f6"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>
                <span style="font-size: 12px; font-weight: 700; color: #3b82f6;">500+ RESTAURANTES</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(245, 158, 11, 0.1); border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.2); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(245, 158, 11, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><path d="M9 12l2 2 4-4m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span style="font-size: 12px; font-weight: 700; color: #f59e0b;">SOPORTE 24/7</span>
            </div>
        </div>

        <!-- Heading principal ultra impactante - MEJORADO CON ANIMACIÓN -->
        <div style="text-align: center; margin-bottom: 48px;">
            <h1 id="hero-title" style="font-size: 72px; line-height: 1.15; margin-bottom: 24px; color: #0f172a; font-weight: 900; letter-spacing: -1px; animation: fadeInUp 0.8s ease-out;">
                El Software que tus <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Clientes Aman</span>
            </h1>
            
            <!-- Nuevo: Subtítulo con animación escalonada -->
            <div style="animation: fadeInUp 1s ease-out 0.2s both;">
                <p style="font-size: 20px; line-height: 1.7; color: #475569; margin-bottom: 24px; max-width: 800px; margin-left: auto; margin-right: auto;">
                    Gestiona pedidos, facturación SUNAT, inventario, caja y delivery en una plataforma inteligente y moderna.
                </p>
                <p style="font-size: 18px; line-height: 1.6; color: #64748b; margin-bottom: 56px; max-width: 700px; margin-left: auto; margin-right: auto;">
                    <strong style="color: #0f172a; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(30, 64, 175, 0.1) 100%); padding: 8px 16px; border-radius: 8px;">Reduce errores en 50%, aumenta eficiencia 30%, y multiplica ingresos en 90 días.</strong>
                </p>
            </div>

            <!-- CTA Buttons profesionales y responsivos - MEJORADOS -->
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 80px;">
                @if (Route::has('filament.admin.auth.login'))
                    @auth
                        <a href="{{ url('/admin') }}" class="hero-cta-primary" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 40px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; text-decoration: none; border-radius: 14px; font-weight: 700; font-size: 16px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 12px 32px rgba(59, 130, 246, 0.3); border: none; cursor: pointer; position: relative; overflow: hidden;" onmouseover="this.style.boxShadow='0 20px 48px rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.3)'; this.style.transform='translateY(0)';" onmousedown="this.style.transform='translateY(-1px)';" onmouseup="this.style.transform='translateY(-4px)';" onfocus="this.style.boxShadow='0 20px 48px rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-4px)';" onblur="this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.3)'; this.style.transform='translateY(0)';">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Ir al Panel
                            <span style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.6s;"></span>
                        </a>
                    @else
                        <a href="{{ route('filament.admin.auth.login') }}" class="hero-cta-primary" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 40px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; text-decoration: none; border-radius: 14px; font-weight: 700; font-size: 16px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 12px 32px rgba(59, 130, 246, 0.3); border: none; cursor: pointer; position: relative; overflow: hidden; animation: pulse 2s infinite;" onmouseover="this.style.boxShadow='0 20px 48px rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.3)'; this.style.transform='translateY(0)';" onmousedown="this.style.transform='translateY(-1px)';" onmouseup="this.style.transform='translateY(-4px)';" onfocus="this.style.boxShadow='0 20px 48px rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-4px)';" onblur="this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.3)'; this.style.transform='translateY(0)';">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Probar Gratis
                            <span style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.6s;"></span>
                        </a>
                    @endauth
                @endif
                <a href="#demo" class="hero-cta-secondary" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 40px; background: white; color: #3b82f6; text-decoration: none; border-radius: 14px; font-weight: 700; font-size: 16px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); border: 2px solid #3b82f6; cursor: pointer; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15); position: relative; overflow: hidden;" onmouseover="this.style.backgroundColor='#eff6ff'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.25)';" onmouseout="this.style.backgroundColor='white'; this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(59, 130, 246, 0.15)';" onmousedown="this.style.transform='translateY(-1px)';" onmouseup="this.style.transform='translateY(-4px)';" onfocus="this.style.backgroundColor='#eff6ff'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.25)';" onblur="this.style.backgroundColor='white'; this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(59, 130, 246, 0.15)';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Agendar Demo Gratuita
                    <span style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent); transition: left 0.6s;"></span>
                </a>
            </div>
        </div>

        <!-- Sección de Imagen - Visual Premium con Efecto Glassmorphism - MEJORADO -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; margin-bottom: 100px;">
            <!-- Left: Dashboard Screenshot Mockup - INTERACTIVO -->
            <div style="position: relative; height: 500px; animation: fadeInLeft 1s ease-out 0.4s both;">
                <div style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 24px; padding: 2px; position: absolute; inset: 0; overflow: hidden; box-shadow: 0 25px 50px rgba(59, 130, 246, 0.25); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 35px 70px rgba(59, 130, 246, 0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 25px 50px rgba(59, 130, 246, 0.25)';">
                    <div style="background: white; border-radius: 22px; height: 100%; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <!-- Dashboard Placeholder Profesional - CON ANIMACIONES -->
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%); display: flex; flex-direction: column; padding: 24px; position: relative;">
                            <!-- Nuevo: Indicador de tiempo real -->
                            <div style="position: absolute; top: 16px; right: 16px; display: flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.1); padding: 4px 8px; border-radius: 12px; font-size: 11px; color: #10b981; font-weight: 600;">
                                <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
                                En Vivo
                            </div>
                            
                            <!-- Header del Dashboard -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">W</div>
                                    <div>
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Dashboard WAYNA</div>
                                        <div style="font-size: 11px; color: #64748b;">Restaurante Premium</div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
                                    <div style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;"></div>
                                    <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></div>
                                </div>
                            </div>
                            
                            <!-- Stats Grid - CON ANIMACIÓN DE NÚMEROS -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                                <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div style="font-size: 11px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Ventas Hoy</div>
                                    <div style="font-size: 24px; font-weight: 800; color: #3b82f6; animation: countUp 2s ease-out;">$3,284</div>
                                    <div style="font-size: 10px; color: #10b981; margin-top: 4px;">↑ 12% vs ayer</div>
                                </div>
                                <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(16, 185, 129, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <div style="font-size: 11px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Pedidos</div>
                                    <div style="font-size: 24px; font-weight: 800; color: #10b981; animation: countUp 2s ease-out 0.2s both;">42</div>
                                    <div style="font-size: 10px; color: #10b981; margin-top: 4px;">↑ 8% vs ayer</div>
                                </div>
                            </div>
                            
                            <!-- Nuevo: Mini gráfico de tendencia -->
                            <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">Tendencia de Ventas</span>
                                    <span style="font-size: 10px; color: #10b981;">↑ 15%</span>
                                </div>
                                <div style="display: flex; align-items: flex-end; gap: 4px; height: 40px;">
                                    <div style="flex: 1; height: 60%; background: linear-gradient(135deg, rgba(59, 130, 246, 0.3) 0%, rgba(59, 130, 246, 0.1) 100%); border-radius: 2px; animation: barGrow 1s ease-out;"></div>
                                    <div style="flex: 1; height: 80%; background: linear-gradient(135deg, rgba(59, 130, 246, 0.5) 0%, rgba(59, 130, 246, 0.2) 100%); border-radius: 2px; animation: barGrow 1s ease-out 0.1s both;"></div>
                                    <div style="flex: 1; height: 70%; background: linear-gradient(135deg, rgba(59, 130, 246, 0.4) 0%, rgba(59, 130, 246, 0.15) 100%); border-radius: 2px; animation: barGrow 1s ease-out 0.2s both;"></div>
                                    <div style="flex: 1; height: 90%; background: linear-gradient(135deg, #3b82f6 0%, rgba(59, 130, 246, 0.3) 100%); border-radius: 2px; animation: barGrow 1s ease-out 0.3s both;"></div>
                                    <div style="flex: 1; height: 85%; background: linear-gradient(135deg, rgba(59, 130, 246, 0.6) 0%, rgba(59, 130, 246, 0.2) 100%); border-radius: 2px; animation: barGrow 1s ease-out 0.4s both;"></div>
                                </div>
                            </div>
                            
                            <!-- Nuevo: Estado del sistema -->
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(16, 185, 129, 0.05); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.1);">
                                <span style="font-size: 11px; color: #64748b;">Sistema</span>
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
                                    <span style="font-size: 10px; color: #10b981; font-weight: 600;">Operativo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Beneficios y Características - MEJORADO CON ANIMACIONES -->
            <div style="animation: fadeInRight 1s ease-out 0.6s both;">
                <h2 style="font-size: 48px; font-weight: 800; color: #0f172a; margin-bottom: 32px; line-height: 1.2;">
                    Todo lo que necesitas<br><span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">en un solo lugar</span>
                </h2>

                <!-- Beneficios con iconos - AHORA INTERACTIVOS -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; border-radius: 12px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.05)'; this.style.transform='translateX(8px)';" onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)';">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.1) 100%)';" onmouseout="this.style.transform='scale(1)'; this.style.background='linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%)';">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Facturación SUNAT 100%</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Emisión automática y segura de facturas y boletas electrónicas. Totalmente validado por SUNAT.</p>
                            <div style="margin-top: 8px;">
                                <span style="font-size: 11px; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 4px;">✓ Compliant</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; border-radius: 12px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(16, 185, 129, 0.05)'; this.style.transform='translateX(8px)';" onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)';">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%)';" onmouseout="this.style.transform='scale(1)'; this.style.background='linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%)';">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Gestión Completa de Pedidos</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Control total de mesas, comandas, tiempos de preparación. Reduce errores y acelera el servicio.</p>
                            <div style="margin-top: 8px;">
                                <span style="font-size: 11px; color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 2px 8px; border-radius: 4px;">⚡ 30% más rápido</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; border-radius: 12px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(245, 158, 11, 0.05)'; this.style.transform='translateX(8px)';" onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)';">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.1) 100%)';" onmouseout="this.style.transform='scale(1)'; this.style.background='linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%)';">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"><path d="M12 2v20m10-10H2"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Inventario en Tiempo Real</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Control de stock automático, alertas de productos agotados y reportes de rentabilidad.</p>
                            <div style="margin-top: 8px;">
                                <span style="font-size: 11px; color: #f59e0b; background: rgba(245, 158, 11, 0.1); padding: 2px 8px; border-radius: 4px;">📊 Automatizado</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; border-radius: 12px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(236, 72, 153, 0.05)'; this.style.transform='translateX(8px)';" onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateX(0)';">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='linear-gradient(135deg, rgba(236, 72, 153, 0.2) 0%, rgba(236, 72, 153, 0.1) 100%)';" onmouseout="this.style.transform='scale(1)'; this.style.background='linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%)';">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2" stroke-linecap="round"><path d="M18.364 5.636l-3.536 3.536m9.172-9.172l-21 21M9 3a6 6 0 100 12 6 6 0 000-12z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Delivery Integrado</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Integración con apps de delivery y seguimiento en tiempo real. Múltiples canales de venta.</p>
                            <div style="margin-top: 8px;">
                                <span style="font-size: 11px; color: #ec4899; background: rgba(236, 72, 153, 0.1); padding: 2px 8px; border-radius: 4px;">🚀 Multi-canal</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Nuevo: CTA adicional para ver más características -->
                <div style="margin-top: 32px; text-align: center;">
                    <a href="#herramientas" style="display: inline-flex; align-items: center; gap: 8px; color: #3b82f6; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s ease;" onmouseover="this.style.gap='12px'; this.style.color='#1e40af';" onmouseout="this.style.gap='8px'; this.style.color='#3b82f6';">
                        Ver todas las características
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14m-7-7l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas y Números finales -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; padding: 60px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 24px;">
            <div style="text-align: center;">
                <div style="font-size: 56px; font-weight: 900; color: white; margin-bottom: 12px;">500+</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255,255,255,0.9); line-height: 1.5;">Restaurantes<br>Activos</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 56px; font-weight: 900; color: white; margin-bottom: 12px;">2.3M+</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255,255,255,0.9); line-height: 1.5;">Facturas<br>Procesadas</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 56px; font-weight: 900; color: white; margin-bottom: 12px;">99.98%</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255,255,255,0.9); line-height: 1.5;">Tasa de<br>Éxito</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 56px; font-weight: 900; color: white; margin-bottom: 12px;">4.9★</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255,255,255,0.9); line-height: 1.5;">Calificación<br>Promedio</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section Profesional 2024 -->
<section class="features" id="herramientas" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 120px 20px;">
    <div class="features-container" style="max-width: 1400px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 100px;">
            <h2 class="section-title" style="font-size: 48px; color: #0f172a; font-weight: 800; margin-bottom: 16px;">
                Características <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Principales</span>
            </h2>
            <p style="font-size: 18px; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.7;">
                Todas las herramientas que tu restaurante necesita para crecer: facturación SUNAT, pedidos, inventario, delivery y mucho más
            </p>
        </div>
        
        <!-- Features Grid - Bento Style -->
        <div class="features-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
            <!-- 1. Facturación SUNAT -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(30, 64, 175, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                        <polyline points="13 2 13 9 20 9"></polyline>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                        <line x1="9" y1="19" x2="15" y2="19"></line>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Facturación SUNAT</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Emisión automática 100% compatible con normativas digitales</p>
            </div>
            
            <!-- 2. Gestión Pedidos -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(16, 185, 129, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                        <path d="M12 7v5l4.25 2.5"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Gestión Pedidos</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Control total de mesas, comandas y tiempo de preparación</p>
            </div>
            
            <!-- 3. Inventario -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(245, 158, 11, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 2L5.12 6.88a2 2 0 0 0-.5 1.66L5 12H2v2h21V2H9z"></path>
                        <path d="M4 22h14a2 2 0 0 0 2-2v-6H4v8a2 2 0 0 0 2 2z"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Inventario</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Control de stock en tiempo real y alertas de productos</p>
            </div>
            
            <!-- 4. Delivery -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(236, 72, 153, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(219, 39, 119, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1"></circle>
                        <path d="M12 8V4M16 12h4M12 16v4M8 12H4M14.828 9.172l2.828-2.828M14.828 14.828l2.828 2.828M9.172 9.172L6.344 6.344M9.172 14.828l-2.828 2.828"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Delivery</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Gestión integrada de entregas y seguimiento en tiempo real</p>
            </div>
            
            <!-- 5. Control Caja -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(139, 92, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(124, 58, 237, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 4V2M8 4V2M2 11h20"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Control Caja</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Gestión completa de transacciones y cuadratura diaria</p>
            </div>
            
            <!-- 6. Reportes -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(6, 182, 212, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(8, 145, 178, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 17"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Reportes</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Análisis y estadísticas detalladas en tiempo real</p>
            </div>
            
            <!-- 7. Múltiples Locales -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(249, 115, 22, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(234, 88, 12, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Múltiples Locales</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Gestión centralizada de todas tus sucursales</p>
            </div>
            
            <!-- 8. Gestión Clientes -->
            <div class="feature-card" style="background: white; padding: 32px 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(132, 204, 22, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; margin: 0 auto 24px; background: linear-gradient(135deg, rgba(132, 204, 22, 0.1) 0%, rgba(101, 163, 13, 0.05) 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#84cc16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; margin-bottom: 8px; color: #0f172a; font-weight: 700;">Gestión Clientes</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Base de datos y programas de fidelización avanzados</p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section 1 - Venta Online Mejorada -->
<section class="content-section" id="mas-info" style="background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);">
    <div class="content-container">
        <div class="content-text" style="padding-right: 40px;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px;">💰 Incrementa Ingresos</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Expande tus Canales de <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Venta</span>
            </h2>
            <p style="font-size: 18px; color: #475569; margin-bottom: 32px; line-height: 1.8; max-width: 550px;">
                Multiplica tus canales de venta con nuestras soluciones integradas de e-commerce, llegando a más clientes y aumentando tus ingresos sin complicaciones operacionales.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 40px;">
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.boxShadow='0 8px 24px rgba(59, 130, 246, 0.1)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 0 0 transparent'; this.style.transform='translateY(0)';">
                    <div style="font-size: 24px; margin-bottom: 12px;">📱</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Menú Online Dinámico</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Actualiza tu menú en tiempo real, ajusta precios y promociones al instante</p>
                </div>
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.boxShadow='0 8px 24px rgba(59, 130, 246, 0.1)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 0 0 transparent'; this.style.transform='translateY(0)';">
                    <div style="font-size: 24px; margin-bottom: 12px;">🔗</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Carta QR Inteligente</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Código QR con análisis, mejora la experiencia del cliente sin contacto</p>
                </div>
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.boxShadow='0 8px 24px rgba(59, 130, 246, 0.1)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 0 0 transparent'; this.style.transform='translateY(0)';">
                    <div style="font-size: 24px; margin-bottom: 12px;">🚚</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Apps de Delivery</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Integración con Uber Eats, Glovo, Rappi y más en un solo lugar</p>
                </div>
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.boxShadow='0 8px 24px rgba(59, 130, 246, 0.1)'; this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='0 0 0 transparent'; this.style.transform='translateY(0)';">
                    <div style="font-size: 24px; margin-bottom: 12px;">📊</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Reportes en Tiempo Real</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Analiza las ventas de cada canal y toma decisiones estratégicas</p>
                </div>
            </div>
            <a href="#demo" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 32px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(59, 130, 246, 0.25); border: none; cursor: pointer;" onmouseover="this.style.boxShadow='0 16px 40px rgba(59, 130, 246, 0.35)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.boxShadow='0 10px 28px rgba(59, 130, 246, 0.25)'; this.style.transform='translateY(0)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Activar Venta Online
            </a>
        </div>
        <div class="content-image" style="position: relative; min-height: 450px; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 120px; margin-bottom: 16px;">🛍️</div>
                <h3 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Múltiples Canales</h3>
                <p style="font-size: 14px; color: #64748b; max-width: 300px;">Manage all your sales channels from one unified dashboard</p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section 2 - Velocidad y Eficiencia -->
<section class="content-section" style="background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%);">
    <div class="content-container reverse" style="grid-template-columns: 1fr 1fr;">
        <div class="content-image" style="position: relative; min-height: 450px; background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden; order: 1;">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 120px; margin-bottom: 16px;">⚡</div>
                <h3 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Velocidad Extrema</h3>
                <p style="font-size: 14px; color: #64748b; max-width: 300px;">Procesa más pedidos en menos tiempo, aumenta rentabilidad</p>
            </div>
        </div>
        <div class="content-text" style="padding-left: 40px; order: 2;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(245, 158, 11, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.5px;">⏱️ Optimiza Procesos</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Acelera Tus <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Operaciones</span>
            </h2>
            <p style="font-size: 18px; color: #475569; margin-bottom: 32px; line-height: 1.8; max-width: 550px;">
                Reduce tiempos de atención en 60%, procesa pedidos al instante y emite comprobantes electrónicos automáticamente sin errores.
            </p>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 40px;">
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">1</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Pedidos al Instante</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Sistema POS optimizado que procesa órdenes en millisegundos</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">2</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Emisión Automática de Facturas</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Genera comprobantes SUNAT sin intervención manual, 100% validado</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">3</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Cero Errores</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Validación inteligente en cada paso, previene problemas tributarios</p>
                    </div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                <div style="padding: 20px; background: white; border-left: 4px solid #f59e0b; border-radius: 8px;">
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase;">Tiempo Promedio Pedido</p>
                    <p style="font-size: 28px; font-weight: 800; color: #0f172a;">12 seg</p>
                </div>
                <div style="padding: 20px; background: white; border-left: 4px solid #f59e0b; border-radius: 8px;">
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase;">Aumento Capacidad</p>
                    <p style="font-size: 28px; font-weight: 800; color: #0f172a;">+60%</p>
                </div>
            </div>
            <a href="#demo" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(245, 158, 11, 0.25); border: none; cursor: pointer;" onmouseover="this.style.boxShadow='0 16px 40px rgba(245, 158, 11, 0.35)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.boxShadow='0 10px 28px rgba(245, 158, 11, 0.25)'; this.style.transform='translateY(0)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Acelera tu Operación
            </a>
        </div>
    </div>
</section>

<!-- Content Section 3 - Inteligencia en Tiempo Real -->
<section class="content-section" style="background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%);">
    <div class="content-container" style="grid-template-columns: 1fr 1fr;">
        <div class="content-text" style="padding-right: 40px;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px;">📊 Datos en Vivo</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Control Total en Cada <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Momento</span>
            </h2>
            <p style="font-size: 18px; color: #475569; margin-bottom: 32px; line-height: 1.8; max-width: 550px;">
                Accede a dashboards inteligentes que te muestran todo: ventas, inventario, estado de entregas y más. Desde tu celular, tablet o computadora, en tiempo real.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 28px; margin-bottom: 12px;">📱</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Mobile Optimizado</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">App nativa para iOS y Android con sincronización instantánea</p>
                </div>
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 28px; margin-bottom: 12px;">🔔</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Alertas Inteligentes</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Notificaciones personalizadas de eventos importantes</p>
                </div>
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 28px; margin-bottom: 12px;">📈</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Gráficos Inteligentes</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Visualiza tendencias y métricas clave de tu negocio</p>
                </div>
                <div style="padding: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 28px; margin-bottom: 12px;">🛡️</div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Seguridad Enterprise</h4>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Encriptación end-to-end, backups automáticos</p>
                </div>
            </div>
            <a href="#demo" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 32px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(59, 130, 246, 0.25); border: none; cursor: pointer;" onmouseover="this.style.boxShadow='0 16px 40px rgba(59, 130, 246, 0.35)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.boxShadow='0 10px 28px rgba(59, 130, 246, 0.25)'; this.style.transform='translateY(0)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Ver Dashboard en Vivo
            </a>
        </div>
        <div class="content-image" style="position: relative; min-height: 450px; background: linear-gradient(135deg, #dbeafe 0%, #f0f9ff 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 120px; margin-bottom: 16px;">📊</div>
                <h3 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Dashboard Inteligente</h3>
                <p style="font-size: 14px; color: #64748b; max-width: 300px;">Monitorea todo tu negocio desde un solo lugar</p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section 4 - Facturación Electrónica Premium -->
<section class="content-section" style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);">
    <div class="content-container reverse" style="grid-template-columns: 1fr 1fr;">
        <div class="content-image" style="position: relative; min-height: 450px; background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden; order: 1;">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 120px; margin-bottom: 16px;">🏛️</div>
                <h3 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Compliance SUNAT</h3>
                <p style="font-size: 14px; color: #64748b; max-width: 300px;">100% Certificado y Validado por SUNAT</p>
            </div>
        </div>
        <div class="content-text" style="padding-left: 40px; order: 2;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(16, 185, 129, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 0.5px;">✅ Cumple Normativa</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Facturación <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">100% SUNAT</span>
            </h2>
            <p style="font-size: 18px; color: #475569; margin-bottom: 32px; line-height: 1.8; max-width: 550px;">
                Emite facturas, boletas y comprobantes electrónicos totalmente validados por SUNAT. Sin riesgos tributarios, sin multas, sin sorpresas.
            </p>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 40px;">
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px;">✓</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Certificado Oficial SUNAT</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Aprobado y certificado directamente por la Superintendencia Nacional de Aduanas</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px;">✓</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Emisión Automática e Instantánea</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Los comprobantes se generan sin retrasos, listos para entregar al cliente</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px;">✓</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Cero Multas y Riesgos</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Garantizamos cumplimiento total. Si algo falla, nosotros asumimos el riesgo</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 16px;">
                    <div style="flex-shrink: 0; width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px;">✓</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Reportes Automáticos</h4>
                        <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Envíos automáticos de comprobantes a SUNAT sin intervención manual</p>
                    </div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                <div style="padding: 20px; background: white; border-left: 4px solid #10b981; border-radius: 8px;">
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase;">Facturas Procesadas</p>
                    <p style="font-size: 28px; font-weight: 800; color: #0f172a;">+2.3M</p>
                </div>
                <div style="padding: 20px; background: white; border-left: 4px solid #10b981; border-radius: 8px;">
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase;">Tasa de Éxito</p>
                    <p style="font-size: 28px; font-weight: 800; color: #0f172a;">99.98%</p>
                </div>
            </div>
            <a href="#demo" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 32px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 15px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(16, 185, 129, 0.25); border: none; cursor: pointer;" onmouseover="this.style.boxShadow='0 16px 40px rgba(16, 185, 129, 0.35)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.boxShadow='0 10px 28px rgba(16, 185, 129, 0.25)'; this.style.transform='translateY(0)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Configurar Facturación
            </a>
        </div>
    </div>
</section>



<!-- Sección Testimonios y Casos de Éxito -->
<section id="testimonios" style="padding: 120px 20px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
    <div style="max-width: 1400px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 80px;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px;">⭐ Lo Que Dicen Nuestros Clientes</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Historias de <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Éxito Real</span>
            </h2>
            <p style="font-size: 18px; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.7;">
                Restaurantes que utilizan Wayna han transformado su operación y aumentado significativamente sus ingresos
            </p>
        </div>

        <!-- Testimonios Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 80px;">
            <!-- Testimonial 1 -->
            <div style="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.05)';">
                <div style="display: flex; gap: 2px; margin-bottom: 16px;">
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                </div>
                <p style="font-size: 16px; color: #0f172a; margin-bottom: 24px; line-height: 1.8; font-weight: 500;">
                    "Con Wayna redujimos el tiempo de atención en un 60%. Nuestros clientes ahora reciben sus pedidos mucho más rápido y eso se reflejó en nuestras ventas."
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px;">MC</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Marco Cabrera</h4>
                        <p style="font-size: 14px; color: #64748b; margin: 0;">Restaurante El Buen Sabor, Lima</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div style="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.05)';">
                <div style="display: flex; gap: 2px; margin-bottom: 16px;">
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                </div>
                <p style="font-size: 16px; color: #0f172a; margin-bottom: 24px; line-height: 1.8; font-weight: 500;">
                    "No tenía que preocuparme más por la facturación. Wayna lo hace automáticamente y con total cumplimiento SUNAT. He ahorrado muchas horas."
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px;">AR</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Andrea Rodríguez</h4>
                        <p style="font-size: 14px; color: #64748b; margin: 0;">Restaurante Las Brumas, Arequipa</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div style="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.05)';">
                <div style="display: flex; gap: 2px; margin-bottom: 16px;">
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                    <span style="color: #f59e0b; font-size: 20px;">★</span>
                </div>
                <p style="font-size: 16px; color: #0f172a; margin-bottom: 24px; line-height: 1.8; font-weight: 500;">
                    "Triplicamos nuestros ingresos en 90 días. Con el menú online y las integraciones con apps de delivery, ahora vendemos tanto en local como online."
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px;">JL</div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Juan López</h4>
                        <p style="font-size: 14px; color: #64748b; margin: 0;">Restaurante Costa Azul, Cusco</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; padding: 60px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 20px;">
            <div style="text-align: center;">
                <div style="font-size: 48px; font-weight: 800; color: white; margin-bottom: 8px;">500+</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255, 255, 255, 0.9);">Restaurantes Activos</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 48px; font-weight: 800; color: white; margin-bottom: 8px;">2.3M+</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255, 255, 255, 0.9);">Facturas Procesadas</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 48px; font-weight: 800; color: white; margin-bottom: 8px;">99.98%</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255, 255, 255, 0.9);">Tasa de Éxito</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 48px; font-weight: 800; color: white; margin-bottom: 8px;">4.9★</div>
                <div style="font-size: 16px; font-weight: 600; color: rgba(255, 255, 255, 0.9);">Calificación Promedio</div>
            </div>
        </div>
    </div>
</section>
<!-- Sección de Pasos para Empezar - Profesional -->
<section id="pasos" style="padding: 120px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
    <div style="max-width: 1400px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 100px;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px;">🚀 Comienza en 4 Pasos</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Tu Transformación Digital <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Comienza Aquí</span>
            </h2>
            <p style="font-size: 18px; color: #64748b; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                Implementa Wayna en tu restaurante en menos de una hora. Nuestro proceso es simple, rápido y totalmente guiado por expertos
            </p>
        </div>

        <!-- Steps Grid -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; margin-bottom: 80px;">
            <!-- Step 1 -->
            <div style="position: relative;">
                <div style="text-align: center;">
                    <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: white; font-size: 48px; font-weight: 800; box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2); position: relative; z-index: 2;">
                        1
                    </div>
                    <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Regístrate Gratis</h3>
                    <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 16px;">
                        Crea tu cuenta en 2 minutos. No requerimos tarjeta de crédito ni datos complicados.
                    </p>
                    <div style="padding: 12px; background: rgba(59, 130, 246, 0.08); border-radius: 8px; text-align: center;">
                        <p style="font-size: 13px; color: #3b82f6; font-weight: 600; margin: 0;">⚡ 2 minutos</p>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div style="position: relative;">
                <div style="text-align: center;">
                    <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: white; font-size: 48px; font-weight: 800; box-shadow: 0 20px 40px rgba(16, 185, 129, 0.2); position: relative; z-index: 2;">
                        2
                    </div>
                    <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Configura Básicos</h3>
                    <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 16px;">
                        Tu nombre, ubicación y datos del restaurante. Un asistente te guía en cada paso.
                    </p>
                    <div style="padding: 12px; background: rgba(16, 185, 129, 0.08); border-radius: 8px; text-align: center;">
                        <p style="font-size: 13px; color: #10b981; font-weight: 600; margin: 0;">⚡ 5 minutos</p>
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div style="position: relative;">
                <div style="text-align: center;">
                    <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: white; font-size: 48px; font-weight: 800; box-shadow: 0 20px 40px rgba(245, 158, 11, 0.2); position: relative; z-index: 2;">
                        3
                    </div>
                    <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Carga tu Menú</h3>
                    <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 16px;">
                        Importa tus productos en lotes o créalos uno a uno. Opción para cargar desde Excel.
                    </p>
                    <div style="padding: 12px; background: rgba(245, 158, 11, 0.08); border-radius: 8px; text-align: center;">
                        <p style="font-size: 13px; color: #f59e0b; font-weight: 600; margin: 0;">⚡ 10 minutos</p>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div style="position: relative;">
                <div style="text-align: center;">
                    <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: white; font-size: 48px; font-weight: 800; box-shadow: 0 20px 40px rgba(236, 72, 153, 0.2); position: relative; z-index: 2;">
                        4
                    </div>
                    <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">¡Listo a Vender!</h3>
                    <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 16px;">
                        Comienza a recibir órdenes. Tu equipo tendrá acceso inmediato al sistema.
                    </p>
                    <div style="padding: 12px; background: rgba(236, 72, 153, 0.08); border-radius: 8px; text-align: center;">
                        <p style="font-size: 13px; color: #ec4899; font-weight: 600; margin: 0;">⚡ Activación inmediata</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 20px; padding: 60px 40px; text-align: center;">
            <h3 style="font-size: 28px; font-weight: 800; color: white; margin-bottom: 16px;">¿Listo para Transformar tu Restaurante?</h3>
            <p style="font-size: 18px; color: rgba(255, 255, 255, 0.9); margin-bottom: 32px; max-width: 700px; margin-left: auto; margin-right: auto;">
                Únete a más de 500 restaurantes que ya están generando más ingresos con Wayna
            </p>
            <a href="#demo" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 40px; background: white; color: #3b82f6; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 16px; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(0, 0, 0, 0.15); border: none; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 16px 40px rgba(0, 0, 0, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 28px rgba(0, 0, 0, 0.15)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Solicitar Demo Gratuita
            </a>
        </div>
    </div>
</section>


<!-- Sección Beneficios por Rol -->
<section style="padding: 120px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
    <div style="max-width: 1400px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 80px;">
            <div style="display: inline-block; padding: 8px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; margin-bottom: 24px;">
                <span style="font-size: 12px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px;">👩‍💼 Personas y Roles</span>
            </div>
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 16px; color: #0f172a; line-height: 1.2;">
                Solución Completa para Tu <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Equipo</span>
            </h2>
            <p style="font-size: 18px; color: #64748b; max-width: 650px; margin: 0 auto; line-height: 1.7;">
                Cada miembro de tu equipo tiene acceso a las herramientas que necesita para hacer su trabajo más fácil y eficiente
            </p>
        </div>

        <!-- Roles Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px;">
            <!-- Role 1: Dueno -->
            <div style="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <span style="font-size: 40px;">💼</span>
                </div>
                <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Para el Dueño</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #3b82f6; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Dashboard ejecutivo con KPIs</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #3b82f6; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Proyecciones de ingresos</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #3b82f6; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Control de múltiples locales</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #3b82f6; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Reportes avanzados</span>
                    </li>
                    <li style="padding: 12px 0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #3b82f6; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Gestin de equipo y permisos</span>
                    </li>
                </ul>
            </div>

            <!-- Role 2: Camarero -->
            <div style="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <span style="font-size: 40px;">👧‍�쵺</span>
                </div>
                <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Para el Mesero</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #10b981; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">POS intuitivo y rápido</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #10b981; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Control de mesas con un click</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #10b981; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Gestuión de pagos simple</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #10b981; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Acceso desde tablet</span>
                    </li>
                    <li style="padding: 12px 0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #10b981; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Soporte en tiempo real</span>
                    </li>
                </ul>
            </div>

            <!-- Role 3: Cocinero -->
            <div style="padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 32px rgba(59, 130, 246, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.05)';">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <span style="font-size: 40px;">👨‍🍳</span>
                </div>
                <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Para la Cocina</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #f59e0b; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Monitor de órdenes en tiempo real</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #f59e0b; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Prioridad de pedidos</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #f59e0b; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Control de inventario</span>
                    </li>
                    <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #f59e0b; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Alertas de ingredientes</span>
                    </li>
                    <li style="padding: 12px 0; display: flex; align-items: start; gap: 12px;">
                        <span style="color: #f59e0b; font-weight: 700; margin-top: 2px;">✓</span>
                        <span style="font-size: 14px; color: #475569;">Gestin de recetas</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<style>
@media (max-width: 768px) {
    .etapa-texto {
        width: 45% !important;
        height: 70px !important;
        padding: 6px 10px !important;
        font-size: 14px !important;
    }
    .etapa-1 { top: 75% !important; left: 25% !important; }
    .etapa-2 { top: 75% !important; left: 75% !important; }
    .etapa-3 { top: 79% !important; left: 25% !important; }
    .etapa-4 { top: 79% !important; left: 75% !important; }
}

@media (max-width: 480px) {
    .etapa-texto {
        width: 48% !important;
        height: 65px !important;
        padding: 5px 8px !important;
        font-size: 14px !important;
    }
    .etapa-1 { top: 74% !important; left: 25% !important; }
    .etapa-2 { top: 74% !important; left: 75% !important; }
    .etapa-3 { top: 78% !important; left: 25% !important; }
    .etapa-4 { top: 78% !important; left: 75% !important; }
}
</style>

<!-- SECCIÓN DEMO/CONTACTO - PREMIUM Y PROFESIONAL -->
<section id="demo" style="padding: 140px 20px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #ffffff 100%); position: relative; overflow: hidden;">
    <!-- Decoración de fondo -->
    <div style="position: absolute; top: -30%; right: -15%; width: 800px; height: 800px; background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    <div style="position: absolute; bottom: -20%; left: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(16, 185, 129, 0.06) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    
    <div style="max-width: 1400px; margin: 0 auto; position: relative; z-index: 1;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center;">
            <!-- LADO IZQUIERDO: CONTENIDO -->
            <div>
                <!-- Badge -->
                <div style="display: inline-block; padding: 8px 16px; background: rgba(59, 130, 246, 0.1); border-radius: 20px; margin-bottom: 24px; border: 1px solid rgba(59, 130, 246, 0.2);">
                    <span style="font-size: 12px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px;">📅 Agenda tu Demo</span>
                </div>

                <!-- Heading -->
                <h2 style="font-size: 56px; font-weight: 900; color: #0f172a; margin-bottom: 24px; line-height: 1.15;">
                    Solicita una <span style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Demo Personalizada</span>
                </h2>

                <!-- Description -->
                <p style="font-size: 18px; color: #475569; margin-bottom: 48px; line-height: 1.8; max-width: 550px;">
                    Descubre cómo Wayna puede transformar tu restaurante. Un especialista te mostrará exactamente cómo funciona y responderá todas tus preguntas sin compromiso.
                </p>

                <!-- Beneficios -->
                <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 48px;">
                    <div style="display: flex; gap: 16px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Demo en Vivo</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Sesión personalizada mostrando exactamente tu caso de uso</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Experto Dedicado</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Especialista en restaurantes responderá todas tus dudas</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Sin Compromiso</h3>
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;">Prueba gratis 14 días sin requerir tarjeta de crédito</p>
                        </div>
                    </div>
                </div>

                <!-- Social Proof -->
                <div style="padding: 24px; background: rgba(59, 130, 246, 0.05); border-left: 4px solid #3b82f6; border-radius: 12px;">
                    <p style="font-size: 14px; color: #0f172a; margin: 0; font-weight: 600;">
                        "Implementamos Wayna en 2 horas. Nuestro equipo está completamente capacitado y productivo."
                    </p>
                    <p style="font-size: 13px; color: #64748b; margin: 12px 0 0 0; font-weight: 500;">
                        — Carlos Mendez, Propietario de 3 restaurantes
                    </p>
                </div>
            </div>

            <!-- LADO DERECHO: FORMULARIO -->
            <div>
                <!-- Card del Formulario con Glassmorphism -->
                <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px); border: 1px solid rgba(59, 130, 246, 0.1); padding: 60px 48px; border-radius: 24px; box-shadow: 0 20px 60px rgba(59, 130, 246, 0.1); position: relative; overflow: hidden;">
                    <!-- Decoración interior -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
                    
                    <div style="position: relative; z-index: 1;">
                        <!-- Header del Formulario -->
                        <div style="text-align: center; margin-bottom: 40px;">
                            <h3 style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Comienza Gratis</h3>
                            <p style="font-size: 15px; color: #64748b; line-height: 1.6;">
                                Un experto te contactará<br>en menos de 24 horas
                            </p>
                        </div>

                        <!-- Formulario -->
                        <form id="demo-form" action="#" method="post" style="display: grid; gap: 20px;">
                            @csrf

                            <!-- Nombre Completo -->
                            <div>
                                <label style="display: block; font-weight: 600; color: #0f172a; margin-bottom: 10px; font-size: 14px;">Nombre Completo *</label>
                                <input type="text" name="name" required placeholder="Juan Pérez García" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s; background: white; color: #0f172a;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>

                            <!-- Email -->
                            <div>
                                <label style="display: block; font-weight: 600; color: #0f172a; margin-bottom: 10px; font-size: 14px;">Email Profesional *</label>
                                <input type="email" name="email" required placeholder="juan@restaurante.com" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s; background: white; color: #0f172a;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>

                            <!-- WhatsApp -->
                            <div>
                                <label style="display: block; font-weight: 600; color: #0f172a; margin-bottom: 10px; font-size: 14px;">WhatsApp *</label>
                                <input type="tel" name="phone" required placeholder="+51 999 888 777" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s; background: white; color: #0f172a;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>

                            <!-- Nombre del Restaurante -->
                            <div>
                                <label style="display: block; font-weight: 600; color: #0f172a; margin-bottom: 10px; font-size: 14px;">Nombre del Restaurante (Opcional)</label>
                                <input type="text" name="restaurant_name" placeholder="Ej: Mi Restaurante - 50 empleados" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.3s; background: white; color: #0f172a;" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>

                            <!-- Botón Submit -->
                            <button type="submit" style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; padding: 16px 24px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1); box-shadow: 0 10px 28px rgba(59, 130, 246, 0.3); margin-top: 8px;" onmouseover="this.style.boxShadow='0 16px 40px rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 10px 28px rgba(59, 130, 246, 0.3)'; this.style.transform='translateY(0)';">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                Solicitar Demo Ahora
                            </button>

                            <!-- Privacy Notice -->
                            <p style="font-size: 12px; color: #64748b; text-align: center; margin-top: 16px; line-height: 1.6;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display: inline; margin-right: 6px; vertical-align: middle;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                Tus datos están seguros con nosotros. No compartimos tu información.
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Trust Badge -->
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 32px; padding-top: 32px; border-top: 1px solid #e2e8f0;">
                    <div style="display: flex; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                    </div>
                    <span style="font-size: 13px; color: #64748b; font-weight: 600;">4.9/5 de 500+ clientes</span>
                </div>
            </div>
        </div>
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