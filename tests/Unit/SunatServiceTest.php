<?php

use App\Services\SunatService;
use App\Helpers\SunatServiceHelper;
use Illuminate\Foundation\Testing\TestCase;

test('SunatService no se inicializa en testing', function () {
    // Configurar SunatService para que no se inicialice
    SunatService::skipInitialization(true);
    
    // Intentar crear una instancia usando el helper
    $service = SunatServiceHelper::createIfNotTesting();
    
    // Verificar que sea null (es decir, no se inicializó)
    expect($service)->toBeNull();
    
    // Restaurar el comportamiento normal
    SunatService::skipInitialization(false);
});
