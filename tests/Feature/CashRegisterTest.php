<?php

use App\Models\CashRegister;
use App\Models\CashRegisterExpense;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// RefreshDatabase ejecuta las migraciones
uses(RefreshDatabase::class);

function createIssuedInvoiceForOrder(Order $order, string $paymentMethod, float $total, array $overrides = []): Invoice
{
    $customer = Customer::factory()->create();
    $taxableAmount = round($total / 1.18, 2);
    $tax = round($total - $taxableAmount, 2);

    return Invoice::create(array_merge([
        'invoice_type' => 'sales_note',
        'series' => 'NVT1',
        'number' => str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
        'issue_date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'taxable_amount' => $taxableAmount,
        'tax' => $tax,
        'total' => $total,
        'tax_authority_status' => 'pending',
        'order_id' => $order->id,
        'payment_method' => $paymentMethod,
        'payment_amount' => $total,
        'change_amount' => 0,
        'advance_payment_received' => 0,
        'pending_balance' => 0,
    ], $overrides));
}

// ============================================
// UC-1: APERTURA DE CAJA
// ============================================

test('usuario autenticado puede abrir una caja con monto inicial', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(500.00, 'Apertura de prueba');

    expect($cashRegister)->toBeInstanceOf(CashRegister::class)
        ->and((float) $cashRegister->opening_amount)->toEqual(500.0)
        ->and($cashRegister->opened_by)->toBe($user->id)
        ->and($cashRegister->is_active)->toBeTrue()
        ->and($cashRegister->opening_datetime)->not->toBeNull();
});

test('no se puede abrir una caja si ya existe una abierta', function () {
    $user = User::factory()->create();
    CashRegister::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    expect(fn () => CashRegister::openRegister(100.00))
        ->toThrow(Exception::class, 'Ya existe una caja abierta. No se puede abrir otra.');
});

test('el monto inicial debe ser mayor o igual a cero', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(0.00);

    expect((float) $cashRegister->opening_amount)->toEqual(0.0);
});

test('no se puede abrir caja con monto inicial negativo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(fn () => CashRegister::openRegister(-1.00))
        ->toThrow(\InvalidArgumentException::class, 'El monto inicial debe ser mayor o igual a cero.');
});

test('la apertura registra opened_by y opening_datetime', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $before = now()->subSecond();
    $cashRegister = CashRegister::openRegister(200.00);
    $after = now()->addSecond();

    expect($cashRegister->opened_by)->toBe($user->id)
        ->and($cashRegister->opening_datetime)->toBeBetween($before, $after);
});

test('hasOpenRegister devuelve true si hay caja abierta', function () {
    CashRegister::factory()->create(['is_active' => true]);

    expect(CashRegister::hasOpenRegister())->toBeTrue();
});

test('hasOpenRegister devuelve false si no hay caja abierta', function () {
    CashRegister::factory()->create(['is_active' => false]);

    expect(CashRegister::hasOpenRegister())->toBeFalse();
});

test('getOpenRegister devuelve la caja abierta', function () {
    $open = CashRegister::factory()->create(['is_active' => true]);
    CashRegister::factory()->create(['is_active' => false]);

    $result = CashRegister::getOpenRegister();

    expect($result->id)->toBe($open->id);
});

test('getOpenRegister devuelve la caja abierta mas reciente', function () {
    $older = CashRegister::factory()->create([
        'is_active' => true,
        'opening_datetime' => now()->subHour(),
    ]);

    $newer = CashRegister::factory()->create([
        'is_active' => true,
        'opening_datetime' => now(),
    ]);

    $result = CashRegister::getOpenRegister();

    expect($result->id)->toBe($newer->id)
        ->and($result->id)->not->toBe($older->id);
});

test('getActiveCashRegisterId devuelve el id de la caja activa', function () {
    $open = CashRegister::factory()->create(['is_active' => true]);

    expect(CashRegister::getActiveCashRegisterId())->toBe($open->id);
});

// ============================================
// UC-2: CIERRE DE CAJA
// ============================================

test('usuario puede cerrar una caja abierta', function () {
    $user = User::factory()->create();
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    $result = $cashRegister->close([
        'actual_cash' => 600.00,
        'expected_cash' => 550.00,
        'difference' => 50.00,
        'notes' => 'Cierre de prueba',
    ]);

    expect($result)->toBeTrue()
        ->and($cashRegister->is_active)->toBeFalse()
        ->and($cashRegister->closed_by)->toBe($user->id)
        ->and($cashRegister->closing_datetime)->not->toBeNull()
        ->and((float) $cashRegister->actual_amount)->toEqual(600.0)
        ->and((float) $cashRegister->difference)->toEqual(50.0);
});

test('el cierre actualiza las observaciones', function () {
    $user = User::factory()->create();
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'observations' => 'Nota inicial',
    ]);

    $this->actingAs($user);

    $cashRegister->close([
        'actual_cash' => 100.00,
        'expected_cash' => 100.00,
        'difference' => 0,
        'notes' => 'Nota de cierre',
    ]);

    expect($cashRegister->observations)->toContain('Nota de cierre');
});

test('status attribute devuelve Abierta cuando is_active es true', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    expect($cashRegister->status)->toBe('Abierta');
});

test('status attribute devuelve Cerrada cuando is_active es false', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => false]);

    expect($cashRegister->status)->toBe('Cerrada');
});

// ============================================
// UC-3: REGISTRO DE VENTAS
// ============================================

test('registro de venta en efectivo incrementa cash_sales', function () {
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'cash_sales' => 0,
    ]);

    $cashRegister->registerSale('cash', 100.00);

    expect((float) $cashRegister->cash_sales)->toEqual(100.0);
});

test('registro de venta con tarjeta incrementa card_sales', function () {
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'card_sales' => 0,
    ]);

    $cashRegister->registerSale('card', 200.00);

    expect((float) $cashRegister->card_sales)->toEqual(200.0);
});

test('registro de venta con otros metodos incrementa other_sales', function () {
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'other_sales' => 0,
    ]);

    $cashRegister->registerSale('yape', 150.00);

    expect((float) $cashRegister->other_sales)->toEqual(150.0);
});

test('total_sales es la suma de cash_card_y_other_sales', function () {
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'cash_sales' => 0,
        'card_sales' => 0,
        'other_sales' => 0,
        'total_sales' => 0,
    ]);

    $cashRegister->registerSale('cash', 100.00);
    $cashRegister->registerSale('card', 200.00);
    $cashRegister->registerSale('yape', 50.00);

    expect((float) $cashRegister->total_sales)->toEqual(350.0);
});

// ============================================
// UC-4: CALCULOS DEL SISTEMA
// ============================================

test('getSystemCashSales suma comprobantes emitidos en efectivo no anulados', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $order1 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order1, 'cash', 100.00);

    $order2 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order2, 'cash', 50.00);

    $order3 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order3, 'cash', 25.00, [
        'tax_authority_status' => 'voided',
        'voided_date' => now()->toDateString(),
    ]);

    expect((float) $cashRegister->getSystemCashSales())->toEqual(150.0);
});

test('getSystemYapeSales suma comprobantes emitidos en yape', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $order1 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order1, 'yape', 80.00);

    $order2 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order2, 'yape', 20.00);

    expect((float) $cashRegister->getSystemYapeSales())->toEqual(100.0);
});

test('getSystemPlinSales suma comprobantes emitidos en plin', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'plin', 75.00);

    expect((float) $cashRegister->getSystemPlinSales())->toEqual(75.0);
});

test('getSystemCardSales suma comprobantes emitidos con tarjeta', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $order1 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order1, 'card', 300.00);

    $order2 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order2, 'credit_card', 150.00);

    expect((float) $cashRegister->getSystemCardSales())->toEqual(450.0);
});

test('getSystemTotalSales suma comprobantes emitidos no anulados', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $order1 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order1, 'cash', 100.00);

    $order2 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order2, 'card', 200.00);

    $order3 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order3, 'yape', 50.00);

    $order4 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order4, 'plin', 30.00);

    $order5 = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order5, 'cash', 25.00, [
        'tax_authority_status' => 'voided',
        'voided_date' => now()->toDateString(),
    ]);

    expect((float) $cashRegister->getSystemTotalSales())->toEqual(380.0);
});

test('calculateExpectedCash calcula apertura mas ventas menos egresos', function () {
    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'opening_amount' => 200.00,
    ]);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'cash', 500.00);

    CashRegisterExpense::create([
        'cash_register_id' => $cashRegister->id,
        'amount' => 100.00,
        'concept' => 'Test expense',
    ]);

    expect((float) $cashRegister->calculateExpectedCash())->toEqual(600.0);
});

test('getCachedExpenses suma todos los egresos', function () {
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    CashRegisterExpense::factory()->create(['cash_register_id' => $cashRegister->id, 'amount' => 50.00]);
    CashRegisterExpense::factory()->create(['cash_register_id' => $cashRegister->id, 'amount' => 30.00]);

    expect((float) $cashRegister->getCachedExpenses())->toEqual(80.0);
});

// ============================================
// UC-5: RECONCILIACION / APROBACION
// ============================================

test('supervisor puede aprobar una caja cerrada', function () {
    $supervisor = User::factory()->create();
    $cashRegister = CashRegister::factory()->create(['is_active' => false, 'is_approved' => false]);

    $this->actingAs($supervisor);

    $result = $cashRegister->reconcile(true, 'Aprobado sin novedad');

    expect($result)->toBeTrue()
        ->and($cashRegister->is_approved)->toBeTrue()
        ->and($cashRegister->approved_by)->toBe($supervisor->id)
        ->and($cashRegister->approval_notes)->toBe('Aprobado sin novedad')
        ->and($cashRegister->approval_datetime)->not->toBeNull();
});

test('no se puede aprobar una caja abierta', function () {
    $user = User::factory()->create();
    $cashRegister = CashRegister::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    expect(fn () => $cashRegister->reconcile(true))
        ->toThrow(Exception::class, 'No se puede reconciliar una caja abierta. Cierre la caja primero.');
});

test('no se puede aprobar una caja ya aprobada', function () {
    $user = User::factory()->create();
    $cashRegister = CashRegister::factory()->create([
        'is_active' => false,
        'is_approved' => true,
    ]);

    $this->actingAs($user);

    expect(fn () => $cashRegister->reconcile(true))
        ->toThrow(Exception::class, 'Esta caja ya ha sido reconciliada y aprobada.');
});

test('reconciliation_status devuelve el estado correcto', function () {
    $abierta = CashRegister::factory()->create(['is_active' => true]);
    $cerradaPendiente = CashRegister::factory()->create(['is_active' => false, 'is_approved' => false]);
    $aprobada = CashRegister::factory()->closed()->approved()->create();

    expect($abierta->reconciliation_status)->toBe('Pendiente de cierre')
        ->and($cerradaPendiente->reconciliation_status)->toBe('Pendiente de reconciliación')
        ->and($aprobada->reconciliation_status)->toBe('Aprobada');
});

// ============================================
// UC-6: RELACIONES
// ============================================

test('cash register tiene relacion con usuario que abrio', function () {
    $user = User::factory()->create();
    $cashRegister = CashRegister::factory()->create(['opened_by' => $user->id]);

    expect($cashRegister->openedBy->id)->toBe($user->id);
});

test('cash register tiene relacion con usuario que cerro', function () {
    $user = User::factory()->create();
    $cashRegister = CashRegister::factory()->closed()->create(['closed_by' => $user->id]);

    expect($cashRegister->closedBy->id)->toBe($user->id);
});

test('cash register tiene relacion con pagos', function () {
    $cashRegister = CashRegister::factory()->create();

    $user = User::factory()->create();
    $order1 = Order::factory()->create();
    Payment::create(['order_id' => $order1->id, 'cash_register_id' => $cashRegister->id, 'payment_method' => 'cash', 'amount' => 10, 'payment_datetime' => now(), 'received_by' => $user->id]);

    $order2 = Order::factory()->create();
    Payment::create(['order_id' => $order2->id, 'cash_register_id' => $cashRegister->id, 'payment_method' => 'cash', 'amount' => 20, 'payment_datetime' => now(), 'received_by' => $user->id]);

    $order3 = Order::factory()->create();
    Payment::create(['order_id' => $order3->id, 'cash_register_id' => $cashRegister->id, 'payment_method' => 'cash', 'amount' => 30, 'payment_datetime' => now(), 'received_by' => $user->id]);

    expect($cashRegister->payments)->toHaveCount(3);
});

test('cash register tiene relacion con egresos', function () {
    $cashRegister = CashRegister::factory()->create();

    CashRegisterExpense::factory()->count(2)->create(['cash_register_id' => $cashRegister->id]);

    expect($cashRegister->expenses)->toHaveCount(2);
});

// ============================================
// UC-3: VERIFICACIÓN DE SALDO AL ABRIR CAJA
// ============================================

test('al abrir caja el saldo esperado es igual al monto inicial', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(500.00, 'Inicio de turno');

    expect((float) $cashRegister->calculateExpectedCash())->toEqual(500.0);
});

test('registrar ventas actualiza el saldo esperado', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(200.00);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'cash', 150.00);

    $cashRegister = CashRegister::find($cashRegister->id);
    expect((float) $cashRegister->calculateExpectedCash())->toEqual(350.0);
});

test('cierre con saldo exacto tiene diferencia cero', function () {
    $user = User::factory()->create();
    $table = \App\Models\Table::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(300.00);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'cash', 400.00);

    $cashRegister = CashRegister::find($cashRegister->id);
    $expectedCash = $cashRegister->calculateExpectedCash();
    expect((float) $expectedCash)->toEqual(700.0);

    $cashRegister = CashRegister::find($cashRegister->id);
    $cashRegister->close([
        'actual_cash' => 700.00,
        'expected_cash' => 700.00,
        'difference' => 0.00,
    ]);

    expect($cashRegister->is_active)->toBeFalse()
        ->and((float) $cashRegister->difference)->toEqual(0.0);
});

test('diferencia_por_pago verifica sobrante en yape', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(100.00);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'yape', 70.00);

    $cashRegister->update(['manual_yape' => 80.00]);
    $cashRegister = CashRegister::find($cashRegister->id);

    $sistema = $cashRegister->getSystemYapeSales();
    $manual = $cashRegister->manual_yape;
    $diff = $manual - $sistema;

    expect($sistema)->toEqual(70.0)
        ->and($manual)->toEqual(80.0)
        ->and($diff)->toEqual(10.0);
});

test('calculo_final_difference contado menos esperado', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::openRegister(100.00);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'cash', 350.00);

    $cashRegister = CashRegister::find($cashRegister->id);
    $cashRegister->update(['manual_yape' => 180.00, 'bill_200' => 1]);

    $expected = $cashRegister->calculateExpectedCash(); // 100 + 350 = 450
    $counted = $cashRegister->calculateTotalCounted(); // 200 + 180 = 380
    $diff = $cashRegister->calculateFinalDifference();

    expect((float) $expected)->toEqual(450.0)
        ->and((float) $counted)->toEqual(380.0)
        ->and((float) $diff)->toEqual(-70.0); // 380 - 450 = -70 (faltante)
});

test('cierre de caja incluye bita express en el total manual', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'opening_amount' => 0.00,
    ]);

    $page = new \App\Filament\Resources\CashRegisterResource\Pages\EditCashRegister;

    $recordProperty = new ReflectionProperty($page, 'record');
    $recordProperty->setAccessible(true);
    $recordProperty->setValue($page, $cashRegister);

    $mutateMethod = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
    $mutateMethod->setAccessible(true);

    $data = $mutateMethod->invoke($page, [
        'manual_yape' => 10.00,
        'manual_plin' => 0.00,
        'manual_card' => 0.00,
        'manual_didi' => 0.00,
        'manual_pedidos_ya' => 0.00,
        'manual_bita_express' => 13.50,
        'manual_otros' => 0.00,
        'bill_200' => 0,
        'bill_100' => 0,
        'bill_50' => 0,
        'bill_20' => 0,
        'bill_10' => 0,
        'coin_5' => 0,
        'coin_2' => 0,
        'coin_1' => 0,
        'coin_050' => 0,
        'coin_020' => 0,
        'coin_010' => 0,
    ]);

    expect((float) $data['actual_amount'])->toEqual(23.5)
        ->and((float) $data['difference'])->toEqual(23.5)
        ->and($data['observations'])->toContain('Bita Express: S/ 13.50');
});

test('cierre de caja usa manual_cash cuando no se ingresan denominaciones', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'opening_amount' => 0.00,
    ]);

    $page = new \App\Filament\Resources\CashRegisterResource\Pages\EditCashRegister;

    $recordProperty = new ReflectionProperty($page, 'record');
    $recordProperty->setAccessible(true);
    $recordProperty->setValue($page, $cashRegister);

    $mutateMethod = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
    $mutateMethod->setAccessible(true);

    $data = $mutateMethod->invoke($page, [
        'manual_cash' => 614.70,
        'manual_yape' => 757.00,
        'manual_plin' => 0.00,
        'manual_card' => 161.30,
        'manual_didi' => 0.00,
        'manual_pedidos_ya' => 0.00,
        'manual_bita_express' => 0.00,
        'manual_otros' => 0.00,
        'bill_200' => 0,
        'bill_100' => 0,
        'bill_50' => 0,
        'bill_20' => 0,
        'bill_10' => 0,
        'coin_5' => 0,
        'coin_2' => 0,
        'coin_1' => 0,
        'coin_050' => 0,
        'coin_020' => 0,
        'coin_010' => 0,
    ]);

    expect((float) $data['actual_amount'])->toEqual(1533.0)
        ->and((float) $data['difference'])->toEqual(1533.0)
        ->and($data['observations'])->toContain('EFECTIVO CONTADO: S/ 614.70');
});

test('cierre de caja justifica faltante con egresos registrados', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'opening_amount' => 0.00,
    ]);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'cash', 100.00);

    CashRegisterExpense::create([
        'cash_register_id' => $cashRegister->id,
        'amount' => 20.00,
        'concept' => 'Compra de bolsas',
    ]);

    $page = new \App\Filament\Resources\CashRegisterResource\Pages\EditCashRegister;

    $recordProperty = new ReflectionProperty($page, 'record');
    $recordProperty->setAccessible(true);
    $recordProperty->setValue($page, $cashRegister);

    $mutateMethod = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
    $mutateMethod->setAccessible(true);

    $data = $mutateMethod->invoke($page, [
        'manual_cash' => 80.00,
        'manual_yape' => 0.00,
        'manual_plin' => 0.00,
        'manual_card' => 0.00,
        'manual_didi' => 0.00,
        'manual_pedidos_ya' => 0.00,
        'manual_bita_express' => 0.00,
        'manual_otros' => 0.00,
        'bill_200' => 0,
        'bill_100' => 0,
        'bill_50' => 0,
        'bill_20' => 0,
        'bill_10' => 0,
        'coin_5' => 0,
        'coin_2' => 0,
        'coin_1' => 0,
        'coin_050' => 0,
        'coin_020' => 0,
        'coin_010' => 0,
    ]);

    expect((float) $data['actual_amount'])->toEqual(80.0)
        ->and((float) $data['expected_amount'])->toEqual(80.0)
        ->and((float) $data['total_expenses'])->toEqual(20.0)
        ->and((float) $data['difference'])->toEqual(0.0);
});

test('cierre de caja exige efectivo contado cuando el sistema reporta ventas en efectivo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cashRegister = CashRegister::factory()->create([
        'is_active' => true,
        'opening_amount' => 0.00,
    ]);

    $order = Order::factory()->create(['cash_register_id' => $cashRegister->id]);
    createIssuedInvoiceForOrder($order, 'cash', 100.00);

    $page = new \App\Filament\Resources\CashRegisterResource\Pages\EditCashRegister;

    $recordProperty = new ReflectionProperty($page, 'record');
    $recordProperty->setAccessible(true);
    $recordProperty->setValue($page, $cashRegister);

    $mutateMethod = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
    $mutateMethod->setAccessible(true);

    expect(fn () => $mutateMethod->invoke($page, [
        'manual_cash' => 0.00,
        'manual_yape' => 0.00,
        'manual_plin' => 0.00,
        'manual_card' => 0.00,
        'manual_didi' => 0.00,
        'manual_pedidos_ya' => 0.00,
        'manual_bita_express' => 0.00,
        'manual_otros' => 0.00,
        'bill_200' => 0,
        'bill_100' => 0,
        'bill_50' => 0,
        'bill_20' => 0,
        'bill_10' => 0,
        'coin_5' => 0,
        'coin_2' => 0,
        'coin_1' => 0,
        'coin_050' => 0,
        'coin_020' => 0,
        'coin_010' => 0,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
