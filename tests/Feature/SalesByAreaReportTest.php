<?php

use App\Models\Area;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\SalesByAreaReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('excluye facturas anuladas o rechazadas y productos sin area o soft-deleted', function () {
    $service = app(SalesByAreaReportService::class);

    $area = Area::create([
        'name' => 'Cocina',
        'slug' => 'cocina',
        'active' => true,
    ]);

    $productValid = createReportProduct($area->id, 'PV001', 'Producto Valido');
    $productWithoutArea = createReportProduct(null, 'PV002', 'Producto Sin Area');
    $productSoftDeleted = createReportProduct($area->id, 'PV003', 'Producto Eliminado', true);

    $invoiceOk = createReportInvoice([
        'invoice_type' => 'receipt',
        'issue_date' => '2026-02-10',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);

    $invoiceRejected = createReportInvoice([
        'invoice_type' => 'invoice',
        'issue_date' => '2026-02-10',
        'tax_authority_status' => 'rejected',
        'sunat_status' => 'RECHAZADO',
    ]);

    $invoiceVoided = createReportInvoice([
        'invoice_type' => 'receipt',
        'issue_date' => '2026-02-10',
        'tax_authority_status' => 'voided',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceOk->id,
        'product_id' => $productValid->id,
        'description' => 'Producto valido',
        'quantity' => 2,
        'unit_price' => 10,
        'subtotal' => 20,
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceOk->id,
        'product_id' => $productWithoutArea->id,
        'description' => 'Sin area',
        'quantity' => 9,
        'unit_price' => 10,
        'subtotal' => 90,
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceOk->id,
        'product_id' => $productSoftDeleted->id,
        'description' => 'Eliminado',
        'quantity' => 8,
        'unit_price' => 10,
        'subtotal' => 80,
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceRejected->id,
        'product_id' => $productValid->id,
        'description' => 'Rechazada',
        'quantity' => 7,
        'unit_price' => 10,
        'subtotal' => 70,
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceVoided->id,
        'product_id' => $productValid->id,
        'description' => 'Anulada',
        'quantity' => 6,
        'unit_price' => 10,
        'subtotal' => 60,
    ]);

    $rows = $service
        ->aggregateQuery('2026-02-01', '2026-02-28', null, 'day')
        ->get();

    expect($rows)->toHaveCount(1)
        ->and((float) $rows->first()->units_sold)->toBe(2.0)
        ->and((float) $rows->first()->net_sold)->toBe(20.0);
});

it('agrupa por mes y permite filtrar por area', function () {
    $service = app(SalesByAreaReportService::class);

    $areaA = Area::create([
        'name' => 'Horno',
        'slug' => 'horno',
        'active' => true,
    ]);

    $areaB = Area::create([
        'name' => 'Bar',
        'slug' => 'bar',
        'active' => true,
    ]);

    $productA = createReportProduct($areaA->id, 'PA001', 'Producto A');
    $productB = createReportProduct($areaB->id, 'PB001', 'Producto B');

    $invoice = createReportInvoice([
        'invoice_type' => 'receipt',
        'issue_date' => '2026-02-15',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoice->id,
        'product_id' => $productA->id,
        'description' => 'A',
        'quantity' => 3,
        'unit_price' => 10,
        'subtotal' => 30,
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoice->id,
        'product_id' => $productB->id,
        'description' => 'B',
        'quantity' => 4,
        'unit_price' => 10,
        'subtotal' => 40,
    ]);

    $rowsAreaA = $service
        ->aggregateQuery('2026-02-01', '2026-02-28', $areaA->id, 'month')
        ->get();

    expect($rowsAreaA)->toHaveCount(1)
        ->and($rowsAreaA->first()->period_key)->toBe('2026-02')
        ->and((float) $rowsAreaA->first()->units_sold)->toBe(3.0)
        ->and((float) $rowsAreaA->first()->net_sold)->toBe(30.0);
});

it('drill-down por periodo y area cuadra con el agregado', function () {
    $service = app(SalesByAreaReportService::class);

    $area = Area::create([
        'name' => 'Parrilla',
        'slug' => Str::slug('Parrilla'),
        'active' => true,
    ]);

    $p1 = createReportProduct($area->id, 'P001', 'Pollo');
    $p2 = createReportProduct($area->id, 'P002', 'Carne');

    $invoice = createReportInvoice([
        'invoice_type' => 'invoice',
        'issue_date' => '2026-02-20',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoice->id,
        'product_id' => $p1->id,
        'description' => 'P1',
        'quantity' => 2,
        'unit_price' => 10,
        'subtotal' => 20,
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoice->id,
        'product_id' => $p2->id,
        'description' => 'P2',
        'quantity' => 5,
        'unit_price' => 10,
        'subtotal' => 50,
    ]);

    $aggregate = $service
        ->aggregateQuery('2026-02-01', '2026-02-28', $area->id, 'day')
        ->first();

    $detailRows = $service
        ->drillDownQuery(
            from: '2026-02-01',
            to: '2026-02-28',
            areaId: $area->id,
            groupBy: 'day',
            period: '2026-02-20',
        )
        ->get();

    $detailUnits = $detailRows->sum(fn ($row) => (float) $row->units_sold);
    $detailNet = $detailRows->sum(fn ($row) => (float) $row->net_sold);

    expect((float) $aggregate->units_sold)->toBe($detailUnits)
        ->and((float) $aggregate->net_sold)->toBe($detailNet);
});

function createReportProduct(?int $areaId, string $code, string $name, bool $softDeleted = false): Product
{
    $category = ProductCategory::create([
        'name' => 'Cat '.$code,
        'description' => 'Categoria test',
        'visible_in_menu' => true,
        'display_order' => 0,
    ]);

    $product = Product::create([
        'code' => $code,
        'name' => $name,
        'description' => 'Test',
        'sale_price' => 10,
        'current_cost' => 5,
        'current_stock' => 0,
        'product_type' => Product::TYPE_SALE_ITEM,
        'category_id' => $category->id,
        'area_id' => $areaId,
        'active' => true,
        'has_recipe' => false,
        'image_path' => null,
        'available' => true,
    ]);

    if ($softDeleted) {
        $product->delete();
    }

    return $product;
}

function createReportInvoice(array $overrides = []): Invoice
{
    $customer = Customer::factory()->create();

    return Invoice::create(array_merge([
        'invoice_type' => 'receipt',
        'series' => 'B001',
        'number' => (string) now()->timestamp.mt_rand(1000, 9999),
        'issue_date' => now()->format('Y-m-d'),
        'customer_id' => $customer->id,
        'taxable_amount' => 10,
        'tax' => 1.8,
        'total' => 11.8,
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ], $overrides));
}
