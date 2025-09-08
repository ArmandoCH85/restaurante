<!-- Formulario simplificado para entrega -->
<div class="delivery-form">
    <h2>Formulario de Entrega</h2>
    <form wire:submit.prevent="submitDelivery">
        <!-- Campo de búsqueda de cliente -->
        <div class="form-group">
            <label for="searchCustomer">Buscar Cliente</label>
            <input type="text" id="searchCustomer" wire:model="searchQuery" placeholder="Ingrese nombre, teléfono o DNI">
            <button type="button" wire:click="searchCustomer">Buscar</button>
        </div>

        <!-- Información del cliente -->
        <div class="form-group">
            <label for="customerName">Nombre</label>
            <input type="text" id="customerName" wire:model="customer.name" placeholder="Nombre del cliente">
        </div>
        <div class="form-group">
            <label for="customerPhone">Teléfono</label>
            <input type="text" id="customerPhone" wire:model="customer.phone" placeholder="Teléfono del cliente">
        </div>
        <div class="form-group">
            <label for="customerAddress">Dirección</label>
            <textarea id="customerAddress" wire:model="customer.address" placeholder="Dirección completa"></textarea>
        </div>

        <!-- Botón de envío -->
        <button type="submit">Guardar y Continuar</button>
    </form>
</div>

<style>
.delivery-form {
    max-width: 500px;
    margin: auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background-color: #0056b3;
}
</style>