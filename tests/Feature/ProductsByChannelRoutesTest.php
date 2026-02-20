<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('permite abrir la ruta legacy de reporte por canal', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/admin/reportes/sales/products_by_channel?dateRange=custom&startDate=2026-02-19&endDate=2026-02-19');

    $response->assertOk();
});

it('requiere autenticacion para descarga excel de products by channel', function () {
    $response = $this->get('/admin/reportes/products-by-channel/excel-download');

    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toContain('login');
});

it('permite descargar excel de products by channel a usuario autenticado', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/admin/reportes/products-by-channel/excel-download?startDate=2026-02-19&endDate=2026-02-19');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
