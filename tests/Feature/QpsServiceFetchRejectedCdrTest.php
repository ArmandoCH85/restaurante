<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\QpsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createRejectedInvoiceWithoutCdr(array $overrides = []): Invoice
{
    $customer = Customer::factory()->create();

    return Invoice::create(array_merge([
        'invoice_type' => 'invoice',
        'series' => 'F001',
        'number' => '00000077',
        'issue_date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'taxable_amount' => 100.00,
        'tax' => 18.00,
        'total' => 118.00,
        'tax_authority_status' => 'pending',
        'sunat_status' => 'RECHAZADO',
        'sunat_code' => '2000',
        'sunat_description' => 'Rechazado por SUNAT',
        'cdr_path' => null,
    ], $overrides));
}

test('recupera cdr de comprobante rechazado sin reenviar', function () {
    config()->set('services.qps.use_dynamic_config', false);
    config()->set('services.qps.base_url', 'https://qpse.test');
    config()->set('services.qps.token_url', 'https://qpse.test/api/auth/cpe/token');
    config()->set('services.qps.api_url', 'https://qpse.test/api/cpe');
    config()->set('services.qps.username', 'usuario');
    config()->set('services.qps.password', 'clave');

    Storage::fake();

    $invoice = createRejectedInvoiceWithoutCdr();

    $cdrXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ApplicationResponse
    xmlns="urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:ResponseCode>2001</cbc:ResponseCode>
    <cbc:Description>Comprobante Rechazado</cbc:Description>
</ApplicationResponse>
XML;

    Http::fake([
        'https://qpse.test/api/auth/cpe/token' => Http::response([
            'token_acceso' => 'token-prueba',
            'expira_en' => 3600,
        ], 200),
        'https://qpse.test/api/cpe/consultar' => Http::response([
            'success' => true,
            'cdr_base64' => base64_encode($cdrXml),
            'code' => '2001',
            'message' => 'Comprobante rechazado',
        ], 200),
        '*' => Http::response([], 404),
    ]);

    $service = new QpsService;
    $result = $service->fetchRejectedInvoiceCdr($invoice);

    expect($result['success'])->toBeTrue();

    $invoice->refresh();

    expect($invoice->cdr_path)->toBe('sunat/cdr/F001-00000077.zip');
    expect($invoice->sunat_status)->toBe('RECHAZADO');
    expect($invoice->sunat_code)->toBe('2001');
    expect($invoice->sunat_description)->toContain('Comprobante Rechazado');

    Storage::disk('local')->assertExists('sunat/cdr/F001-00000077.zip');

    Http::assertSent(function (Request $request): bool {
        if ($request->url() !== 'https://qpse.test/api/cpe/consultar') {
            return false;
        }

        $payload = $request->data();
        $externalId = $payload['external_id'] ?? $payload['nombre_archivo'] ?? null;

        return $externalId === '20000000000-01-F001-77';
    });

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), '/api/cpe/enviar'));
});
