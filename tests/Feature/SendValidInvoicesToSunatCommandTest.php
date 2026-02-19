<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\QpsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createInvoiceForSunatCommand(array $overrides = []): Invoice
{
    $customer = Customer::factory()->create();

    return Invoice::create(array_merge([
        'invoice_type' => 'invoice',
        'series' => 'F001',
        'number' => str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
        'issue_date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'taxable_amount' => 100.00,
        'tax' => 18.00,
        'total' => 118.00,
        'tax_authority_status' => 'pending',
        'sunat_status' => 'PENDIENTE',
        'voided_date' => null,
    ], $overrides));
}

test('comando envia solo boletas y facturas validas no anuladas', function () {
    $shouldSendInvoice = createInvoiceForSunatCommand([
        'invoice_type' => 'invoice',
        'series' => 'F001',
        'sunat_status' => 'PENDIENTE',
        'tax_authority_status' => 'pending',
    ]);

    $shouldSendReceipt = createInvoiceForSunatCommand([
        'invoice_type' => 'receipt',
        'series' => 'B001',
        'sunat_status' => null,
        'tax_authority_status' => 'accepted',
    ]);

    createInvoiceForSunatCommand([
        'invoice_type' => 'receipt',
        'series' => 'NV001',
        'sunat_status' => 'PENDIENTE',
    ]);

    createInvoiceForSunatCommand([
        'invoice_type' => 'invoice',
        'series' => 'F002',
        'sunat_status' => 'ACEPTADO',
    ]);

    createInvoiceForSunatCommand([
        'invoice_type' => 'invoice',
        'series' => 'F003',
        'tax_authority_status' => 'voided',
    ]);

    createInvoiceForSunatCommand([
        'invoice_type' => 'receipt',
        'series' => 'B003',
        'voided_date' => now()->toDateString(),
    ]);

    $processedIds = [];

    $qpsMock = \Mockery::mock(QpsService::class);
    $qpsMock->shouldReceive('sendInvoiceViaQps')
        ->twice()
        ->andReturnUsing(function (Invoice $invoice) use (&$processedIds) {
            $processedIds[] = $invoice->id;

            return [
                'success' => true,
                'message' => 'ok',
            ];
        });

    $this->app->instance(QpsService::class, $qpsMock);

    $this->artisan('sunat:send-valid-invoices', ['--force' => true])
        ->assertExitCode(0);

    expect($processedIds)->toEqualCanonicalizing([
        $shouldSendInvoice->id,
        $shouldSendReceipt->id,
    ]);
});

test('comando en dry-run no envia comprobantes', function () {
    createInvoiceForSunatCommand([
        'invoice_type' => 'invoice',
        'series' => 'F010',
        'sunat_status' => 'PENDIENTE',
    ]);

    $qpsMock = \Mockery::mock(QpsService::class);
    $qpsMock->shouldReceive('sendInvoiceViaQps')->never();
    $this->app->instance(QpsService::class, $qpsMock);

    $this->artisan('sunat:send-valid-invoices', ['--dry-run' => true])
        ->assertExitCode(0);
});
