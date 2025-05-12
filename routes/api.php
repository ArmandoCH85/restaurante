Route::middleware('auth:sanctum')->get('/current-cash-register', function () {
    $cashRegister = \App\Models\CashRegister::getOpenRegister();

    if ($cashRegister) {
        return response()->json([
            'success' => true,
            'cash_register_id' => $cashRegister->id
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'No hay una caja abierta actualmente'
    ]);
});
