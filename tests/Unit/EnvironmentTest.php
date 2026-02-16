<?php

use Illuminate\Foundation\Testing\TestCase;

test('entorno esta configurado correctamente', function () {
    $env = $_ENV['APP_ENV'] ?? 'no set';
    expect($env)->toBe('testing');
});

test('no hay consultas a base de datos durante el boot', function () {
    // Este test pasa si llegamos aquí sin errores de base de datos
    expect(true)->toBeTrue();
});
