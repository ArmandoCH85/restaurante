<!-- Modal de Cobro -->
<div id="cashierModal"
    x-data="{
        isVisible: false,
        totalAmount: 0,
        paymentMethod: 'cash',
        receivedAmount: 0,
        changeAmount: 0,
        referenceNumber: '',
        cashRegisterId: null,
        paymentInProgress: false,

        init() {
            this.$watch('receivedAmount', (value) => {
                this.calculateChange();
            });
        },

        open(amount) {
            this.totalAmount = amount;
            this.receivedAmount = amount;
            this.paymentMethod = 'cash';
            this.referenceNumber = '';
            this.calculateChange();
            this.isVisible = true;
            this.getCashRegisterId();
        },

        calculateChange() {
            if (this.paymentMethod === 'cash') {
                this.changeAmount = Math.max(0, this.receivedAmount - this.totalAmount).toFixed(2);
            } else {
                this.changeAmount = 0;
            }
        },

        getCashRegisterId() {
            fetch('/api/current-cash-register')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.cashRegisterId = data.cash_register_id;
                    } else {
                        // Mostrar alerta si no hay caja abierta
                        Swal.fire({
                            title: 'Error',
                            text: 'No hay una caja abierta. Debe abrir una caja para procesar pagos.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                        this.isVisible = false;
                    }
                });
        },

        processPayment() {
            if (this.paymentInProgress) return;

            if (!this.cashRegisterId) {
                Swal.fire({
                    title: 'Error',
                    text: 'No hay una caja abierta. Debe abrir una caja para procesar pagos.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (this.paymentMethod !== 'cash' && !this.referenceNumber) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe ingresar un número de referencia para este método de pago.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Convertir a números con redondeo apropiado para evitar problemas de precisión
            const received = Math.round(parseFloat(this.receivedAmount) * 100) / 100;
            const total = Math.round(parseFloat(this.totalAmount) * 100) / 100;
            
            if (this.paymentMethod === 'cash' && received < total) {
                Swal.fire({
                    title: 'Error',
                    text: 'El monto recibido debe ser mayor o igual al total a pagar.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            this.paymentInProgress = true;

            // Datos para enviar al servidor
            const paymentData = {
                order_id: window.currentOrderId,
                cash_register_id: this.cashRegisterId,
                payment_method: this.paymentMethod,
                amount: this.totalAmount,
                received_amount: this.receivedAmount,
                reference_number: this.referenceNumber,
                payment_datetime: new Date().toISOString(),
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            // Enviar al servidor
            fetch('/pos/process-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(paymentData)
            })
            .then(response => response.json())
            .then(data => {
                this.paymentInProgress = false;

                if (data.success) {
                    this.isVisible = false;

                    // Mostrar confirmación
                    Swal.fire({
                        title: 'Pago Exitoso',
                        text: 'El pago ha sido procesado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Imprimir Recibo',
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('/pos/invoice/print/' + data.invoice_id, '_blank');
                        }
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Ha ocurrido un error al procesar el pago.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            })
            .catch(error => {
                this.paymentInProgress = false;
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ha ocurrido un error al procesar el pago.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            });
        }
    }"
    x-show="isVisible"
    @open-cashier-modal.window="open($event.detail.amount)"
    class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto flex items-center justify-center z-50"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;">

    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95">

        <div class="bg-amber-600 text-white py-4 px-6 rounded-t-lg flex justify-between items-center">
            <h3 class="text-xl font-bold">Procesar Pago</h3>
            <button @click="isVisible = false" class="text-white hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div class="mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-700 font-semibold">Total a Pagar:</span>
                    <span class="text-2xl font-bold">S/ <span x-text="totalAmount.toFixed(2)"></span></span>
                </div>

                <div class="border-t border-b py-2 my-3">
                    <label class="block text-gray-700 font-semibold mb-2">Método de Pago</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="paymentMethod" value="cash" x-model="paymentMethod" class="form-radio text-amber-600">
                            <span>Efectivo</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="paymentMethod" value="credit_card" x-model="paymentMethod" class="form-radio text-amber-600">
                            <span>Tarjeta de Crédito</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="paymentMethod" value="debit_card" x-model="paymentMethod" class="form-radio text-amber-600">
                            <span>Tarjeta de Débito</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="paymentMethod" value="digital_wallet" x-model="paymentMethod" class="form-radio text-amber-600">
                            <span>Billetera Digital</span>
                        </label>
                    </div>
                </div>

                <div x-show="paymentMethod === 'cash'" class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Monto Recibido</label>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">S/</span>
                        <input
                            type="number"
                            x-model="receivedAmount"
                            min="0"
                            step="0.01"
                            @input="receivedAmount = parseFloat($event.target.value).toFixed(2)"
                            class="w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div x-show="paymentMethod === 'cash'" class="mb-4 bg-gray-100 p-3 rounded-md">
                    <div class="flex justify-between">
                        <span class="text-gray-700 font-semibold">Cambio:</span>
                        <span class="text-xl font-bold text-green-600">S/ <span x-text="changeAmount"></span></span>
                    </div>
                </div>

                <div x-show="paymentMethod !== 'cash'" class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Número de Referencia</label>
                    <input
                        type="text"
                        x-model="referenceNumber"
                        placeholder="Ingrese el número de referencia o voucher"
                        class="w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div class="flex justify-between space-x-4">
                <button
                    @click="isVisible = false"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 flex-1">
                    Cancelar
                </button>
                <button
                    @click="processPayment()"
                    :disabled="paymentInProgress"
                    class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 flex-1 flex items-center justify-center">
                    <span x-show="!paymentInProgress">Procesar Pago</span>
                    <span x-show="paymentInProgress" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
