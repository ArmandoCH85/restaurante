{{-- 
    POS Login Header Component
    Minimal, responsive, and consistent with existing styles.
    Compatible with Filament 3.x
--}}

<div class="pos-header">
    <img
        src="{{ asset('images/logoWayna.svg') }}"
        alt="Logo Wayna POS"
        class="pos-logo"
        loading="eager"
    >

    <h1 class="pos-title">Acceso administrativo</h1>
    <p class="pos-subtitle">Ingresa tu codigo de 6 digitos</p>

    <div class="pos-security-note">
        <span class="pos-security-dot" aria-hidden="true"></span>
        <span>Conexion segura</span>
    </div>
</div>
