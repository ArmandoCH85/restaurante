<?php

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductsByChannelProfitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calcula la ganancia por canal usando comprobantes', function () {
    $service = app(ProductsByChannelProfitService::class);

    $productA = createChannelProfitProduct('PRD-A', 5);
    $productB = createChannelProfitProduct('PRD-B', 4);

    $orderDineIn = createChannelProfitOrder('dine_in', '2026-02-19 10:15:00');
    $invoiceDineIn = createChannelProfitInvoice($orderDineIn, [
        'series' => 'B001',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceDineIn->id,
        'product_id' => $productA->id,
        'description' => 'Producto A',
        'quantity' => 2,
        'unit_price' => 15,
        'subtotal' => 30,
    ]);

    $orderDelivery = createChannelProfitOrder('delivery', '2026-02-19 11:30:00');
    $invoiceDelivery = createChannelProfitInvoice($orderDelivery, [
        'series' => 'F001',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceDelivery->id,
        'product_id' => $productB->id,
        'description' => 'Producto B',
        'quantity' => 1,
        'unit_price' => 10,
        'subtotal' => 10,
    ]);

    $rows = $service->getByChannel(
        Carbon::parse('2026-02-19 00:00:00'),
        Carbon::parse('2026-02-19 23:59:59')
    );

    $dineIn = $rows->firstWhere('service_type', 'dine_in');
    $delivery = $rows->firstWhere('service_type', 'delivery');

    expect($rows)->toHaveCount(2)
        ->and((float) $dineIn->total_quantity)->toBe(2.0)
        ->and((float) $dineIn->total_sales)->toBe(30.0)
        ->and((float) $dineIn->total_cost)->toBe(10.0)
        ->and((float) $dineIn->total_profit)->toBe(20.0)
        ->and((float) $delivery->total_quantity)->toBe(1.0)
        ->and((float) $delivery->total_sales)->toBe(10.0)
        ->and((float) $delivery->total_cost)->toBe(4.0)
        ->and((float) $delivery->total_profit)->toBe(6.0);
});

it('excluye comprobantes anulados y rechazados', function () {
    $service = app(ProductsByChannelProfitService::class);

    $product = createChannelProfitProduct('PRD-C', 3);

    $orderOk = createChannelProfitOrder('takeout', '2026-02-19 09:00:00');
    $invoiceOk = createChannelProfitInvoice($orderOk, [
        'series' => 'B001',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceOk->id,
        'product_id' => $product->id,
        'description' => 'Valido',
        'quantity' => 1,
        'unit_price' => 10,
        'subtotal' => 10,
    ]);

    $orderVoided = createChannelProfitOrder('takeout', '2026-02-19 10:00:00');
    $invoiceVoided = createChannelProfitInvoice($orderVoided, [
        'series' => 'B002',
        'tax_authority_status' => 'voided',
        'voided_date' => '2026-02-19',
        'sunat_status' => 'ACEPTADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceVoided->id,
        'product_id' => $product->id,
        'description' => 'Anulado',
        'quantity' => 20,
        'unit_price' => 10,
        'subtotal' => 200,
    ]);

    $orderRejected = createChannelProfitOrder('takeout', '2026-02-19 11:00:00');
    $invoiceRejected = createChannelProfitInvoice($orderRejected, [
        'series' => 'F002',
        'tax_authority_status' => 'rejected',
        'sunat_status' => 'RECHAZADO',
    ]);

    InvoiceDetail::create([
        'invoice_id' => $invoiceRejected->id,
        'product_id' => $product->id,
        'description' => 'Rechazado',
        'quantity' => 20,
        'unit_price' => 10,
        'subtotal' => 200,
    ]);

    $rows = $service->getByChannel(
        Carbon::parse('2026-02-19 00:00:00'),
        Carbon::parse('2026-02-19 23:59:59')
    );

    $takeout = $rows->firstWhere('service_type', 'takeout');

    expect($rows)->toHaveCount(1)
        ->and((float) $takeout->total_sales)->toBe(10.0)
        ->and((float) $takeout->total_profit)->toBe(7.0);
});

it('filtra por tipo de comprobante segun serie', function () {
    $service = app(ProductsByChannelProfitService::class);
    $product = createChannelProfitProduct('PRD-D', 2);

    $orderSalesNote = createChannelProfitOrder('dine_in', '2026-02-19 12:00:00');
    $invoiceSalesNote = createChannelProfitInvoice($orderSalesNote, [
        'series' => 'NV001',
        'tax_authority_status' => 'accepted',
        'sunat_status' => null,
    ]);
    InvoiceDetail::create([
        'invoice_id' => $invoiceSalesNote->id,
        'product_id' => $product->id,
        'description' => 'NV',
        'quantity' => 1,
        'unit_price' => 10,
        'subtotal' => 10,
    ]);

    $orderReceipt = createChannelProfitOrder('dine_in', '2026-02-19 12:30:00');
    $invoiceReceipt = createChannelProfitInvoice($orderReceipt, [
        'series' => 'B001',
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ]);
    InvoiceDetail::create([
        'invoice_id' => $invoiceReceipt->id,
        'product_id' => $product->id,
        'description' => 'B',
        'quantity' => 1,
        'unit_price' => 10,
        'subtotal' => 10,
    ]);

    $rows = $service->getByChannel(
        Carbon::parse('2026-02-19 00:00:00'),
        Carbon::parse('2026-02-19 23:59:59'),
        channelFilter: null,
        invoiceType: 'sales_note'
    );

    $dineIn = $rows->firstWhere('service_type', 'dine_in');

    expect($rows)->toHaveCount(1)
        ->and((float) $dineIn->total_sales)->toBe(10.0)
        ->and((float) $dineIn->total_profit)->toBe(8.0);
});

function createChannelProfitProduct(string $code, float $cost): Product
{
    $category = ProductCategory::create([
        'name' => "Categoria {$code}",
        'description' => 'Categoria test',
        'parent_category_id' => null,
        'visible_in_menu' => true,
        'display_order' => 0,
    ]);

    return Product::create([
        'code' => $code,
        'name' => "Producto {$code}",
        'description' => 'Producto test',
        'sale_price' => 10,
        'category_id' => $category->id,
        'product_type' => Product::TYPE_SALE_ITEM,
        'current_stock' => 0,
        'current_cost' => $cost,
        'active' => true,
        'has_recipe' => false,
        'image_path' => null,
        'available' => true,
    ]);
}

function createChannelProfitOrder(string $serviceType, string $orderDatetime): Order
{
    $employee = Employee::factory()->create();

    return Order::factory()->billed()->create([
        'service_type' => $serviceType,
        'employee_id' => $employee->id,
        'order_datetime' => $orderDatetime,
        'status' => 'completed',
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
    ]);
}

function createChannelProfitInvoice(Order $order, array $overrides = []): Invoice
{
    $customer = Customer::factory()->create();

    return Invoice::create(array_merge([
        'invoice_type' => 'receipt',
        'series' => 'B001',
        'number' => (string) now()->timestamp.random_int(1000, 9999),
        'issue_date' => now()->format('Y-m-d'),
        'customer_id' => $customer->id,
        'order_id' => $order->id,
        'taxable_amount' => 0,
        'tax' => 0,
        'total' => 0,
        'tax_authority_status' => 'accepted',
        'sunat_status' => 'ACEPTADO',
    ], $overrides));
}
