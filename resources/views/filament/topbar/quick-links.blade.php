@php($user = auth()->user())
@php($roles = $user?->getRoleNames()->toArray() ?? [])
<div class="flex items-center gap-2" id="quick-links-wrapper" data-roles="{{ implode(',', $roles) }}">
    @if($user && $user->hasAnyRole(['super_admin','admin','cashier']))
        <a id="ql-pos" href="{{ url('/admin/pos-interface') }}" title="Venta Directa"
           class="ql-action"
           style="--ql-bg:#2563eb;--ql-bg-hover:#1d4ed8;--ql-ring:#3b82f6;">
            <x-heroicon-o-credit-card class="w-4 h-4" />
        </a>
    @endif
    @if($user && $user->hasAnyRole(['super_admin','admin','waiter','cashier']))
        <a id="ql-mapas" href="{{ url('/admin/mapa-mesas') }}" title="Mapa de Mesas"
           class="ql-action"
           style="--ql-bg:#059669;--ql-bg-hover:#047857;--ql-ring:#34d399;">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3V6" />
                <path d="M15 6v15" />
            </svg>
        </a>
    @endif
</div>

<style>
    .ql-action {position:relative;display:inline-flex;align-items:center;justify-content:center;height:2rem;width:2rem;border-radius:0.5rem;background:var(--ql-bg);color:#fff!important;font-size:.75rem;font-weight:600;line-height:1;transition:background .18s ease, box-shadow .18s ease;box-shadow:0 0 0 1px rgba(0,0,0,.05)}
    .ql-action:hover{background:var(--ql-bg-hover)}
    .ql-action:focus{outline:none;box-shadow:0 0 0 2px var(--ql-ring),0 0 0 4px rgba(255,255,255,.6)}
    .ql-action svg{width:1rem;height:1rem;stroke:currentColor;stroke-width:2;}
    :root:not(.dark) #ql-mapas svg{color:#fff;stroke:#fff;}
    body:not(.dark) #ql-mapas svg path{stroke:#fff;}
    /* Forzar background en caso de override */
    #ql-mapas{background:var(--ql-bg)!important;}
    #ql-mapas:hover{background:var(--ql-bg-hover)!important;}
</style>
