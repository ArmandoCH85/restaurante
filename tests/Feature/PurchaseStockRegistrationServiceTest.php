<?php

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PurchaseStockRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calcula total de compra como subtotal mas impuesto monto', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->default()->create([
        'created_by' => $user->id,
    ]);

    $purchase = Purchase::create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
        'purchase_date' => now()->toDateString(),
        'document_number' => 'F001-1001',
        'document_type' => Purchase::DOCUMENT_TYPE_INVOICE,
        'subtotal' => 200.00,
        'tax' => 18.00,
        'total' => 0,
        'status' => Purchase::STATUS_PENDING,
        'created_by' => $user->id,
        'notes' => 'Test total',
    ]);

    expect((float) $purchase->fresh()->total)->toBe(218.0);
});

it('registra movimiento de compra para ingrediente y actualiza stock sin errores', function () {
    $user = User::factory()->create();
    $warehouse = Warehouse::factory()->default()->create([
        'created_by' => $user->id,
    ]);

    $category = createTestCategory('Ingredientes Test');
    $product = createTestProduct([
        'code' => 'ING-TEST-001',
        'name' => 'Ingrediente Test',
        'category_id' => $category->id,
        'product_type' => Product::TYPE_INGREDIENT,
        'current_stock' => 0,
        'current_cost' => 1.50,
        'sale_price' => 0,
    ]);

    Ingredient::factory()->create([
        'code' => 'ING-TEST-001',
        'current_stock' => 0,
        'current_cost' => 1.50,
    ]);

    $movement = InventoryMovement::createPurchaseMovement(
        productId: $product->id,
        warehouseId: $warehouse->id,
        quantity: 2,
        unitCost: 5,
        purchaseId: 999,
        referenceDocument: 'F001-999',
        createdBy: $user->id,
        notes: 'Prueba movimiento ingrediente'
    );

    expect($movement->id)->not->toBeNull()
        ->and((float) $product->fresh()->current_stock)->toBe(2.0);
});

it('registra stock de compra una sola vez por detalle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->default()->create([
        'created_by' => $user->id,
    ]);

    $category = createTestCategory('Venta Test');
    $product = createTestProduct([
        'code' => 'PRD-TEST-001',
        'name' => 'Producto Test',
        'category_id' => $category->id,
        'product_type' => Product::TYPE_SALE_ITEM,
        'current_stock' => 0,
        'current_cost' => 3,
        'sale_price' => 12,
    ]);

    $purchase = Purchase::create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
        'purchase_date' => now()->toDateString(),
        'document_number' => 'F001-1002',
        'document_type' => Purchase::DOCUMENT_TYPE_INVOICE,
        'subtotal' => 20.00,
        'tax' => 0.00,
        'total' => 0,
        'status' => Purchase::STATUS_PENDING,
        'created_by' => $user->id,
        'notes' => 'Test idempotencia',
    ]);

    PurchaseDetail::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_cost' => 10,
        'subtotal' => 20,
    ]);

    $service = app(PurchaseStockRegistrationService::class);
    $service->register($purchase->fresh());
    $service->register($purchase->fresh());

    $movementCount = InventoryMovement::query()
        ->where('reference_type', Purchase::class)
        ->where('reference_id', $purchase->id)
        ->where('movement_type', InventoryMovement::TYPE_PURCHASE)
        ->count();

    expect($movementCount)->toBe(1)
        ->and((float) $product->fresh()->current_stock)->toBe(2.0);
});

function createTestCategory(string $name): ProductCategory
{
    return ProductCategory::create([
        'name' => $name,
        'description' => 'Categoria para pruebas',
        'parent_category_id' => null,
        'visible_in_menu' => true,
        'display_order' => 0,
    ]);
}

function createTestProduct(array $overrides = []): Product
{
    return Product::create(array_merge([
        'code' => 'PRD-'.random_int(1000, 9999),
        'name' => 'Producto de prueba',
        'description' => 'Producto para pruebas',
        'sale_price' => 10,
        'current_cost' => 2,
        'current_stock' => 0,
        'product_type' => Product::TYPE_SALE_ITEM,
        'category_id' => createTestCategory('General Test')->id,
        'active' => true,
        'has_recipe' => false,
        'image_path' => null,
        'available' => true,
    ], $overrides));
}
