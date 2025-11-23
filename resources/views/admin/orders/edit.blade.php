@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Pedido #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Volver
        </a>
    </div>

    <form action="{{ route('admin.orders.update', $order) }}" method="POST" id="orderForm">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="md:col-span-2 space-y-6">
                <!-- Items -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Ítems del Pedido</h2>
                    
                    <div id="items-container" class="space-y-4">
                        <!-- Items will be added here by JS -->
                    </div>

                    <button type="button" onclick="addItem()" class="mt-4 text-sm text-brand-pink hover:text-brand-heart font-medium">
                        + Agregar Producto
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Client & Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-4">
                        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select name="client_id" id="client_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                            <option value="">Seleccionar Cliente</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $order->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="datetime-local" name="date" id="date" value="{{ $order->date->format('Y-m-d\TH:i') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                            <option value="draft" {{ $order->status == 'draft' ? 'selected' : '' }}>Borrador</option>
                            <option value="reserved" {{ $order->status == 'reserved' ? 'selected' : '' }}>Reservado</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Pagado</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                </div>

                <!-- Payment & Shipping -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-4">
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                        <select name="payment_method" id="payment_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <option value="">Seleccionar...</option>
                            <option value="cash" {{ $order->payment_method == 'cash' ? 'selected' : '' }}>Efectivo</option>
                            <option value="transfer" {{ $order->payment_method == 'transfer' ? 'selected' : '' }}>Transferencia</option>
                            <option value="mercadopago" {{ $order->payment_method == 'mercadopago' ? 'selected' : '' }}>Mercado Pago</option>
                            <option value="other" {{ $order->payment_method == 'other' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="delivery_type" class="block text-sm font-medium text-gray-700 mb-1">Entrega</label>
                        <select name="delivery_type" id="delivery_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <option value="showroom" {{ $order->delivery_type == 'showroom' ? 'selected' : '' }}>Retiro en Showroom</option>
                            <option value="shipping" {{ $order->delivery_type == 'shipping' ? 'selected' : '' }}>Envío</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="shipping_cost" class="block text-sm font-medium text-gray-700 mb-1">Costo de Envío</label>
                        <input type="number" name="shipping_cost" id="shipping_cost" value="{{ $order->shipping_cost }}" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" onchange="calculateTotal()">
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                        <span>Total:</span>
                        <span id="total-display">${{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                    <button type="submit" class="w-full mt-4 bg-brand-pink text-white px-6 py-3 rounded-md hover:bg-brand-heart transition-colors font-medium">
                        Actualizar Pedido
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const products = @json($products);
    const existingItems = @json($order->items);
    let itemCount = 0;

    function addItem(item = null) {
        const index = itemCount++;
        const productId = item ? item.product_id : '';
        const variationId = item ? item.variation_id : '';
        const quantity = item ? item.quantity : 1;
        const price = item ? item.unit_price : '';

        const html = `
            <div class="grid grid-cols-12 gap-4 items-end border-b border-gray-100 pb-4 item-row" id="item-${index}">
                <div class="col-span-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>
                    <select name="items[${index}][product_id]" class="w-full rounded-md border-gray-300 shadow-sm text-sm" onchange="loadVariations(this, ${index})">
                        <option value="">Seleccionar...</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.price}" ${p.id == productId ? 'selected' : ''}>${p.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Variación</label>
                    <select name="items[${index}][variation_id]" class="w-full rounded-md border-gray-300 shadow-sm text-sm" ${!productId ? 'disabled' : ''} id="variation-${index}">
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                    <input type="number" name="items[${index}][quantity]" value="${quantity}" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm" onchange="calculateTotal()">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                    <input type="number" name="items[${index}][unit_price]" value="${price}" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm text-sm" onchange="calculateTotal()" id="price-${index}">
                </div>
                <div class="col-span-1">
                    <button type="button" onclick="removeItem(${index})" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('items-container').insertAdjacentHTML('beforeend', html);

        if (productId) {
            const select = document.querySelector(`#item-${index} select[name*="[product_id]"]`);
            loadVariations(select, index, variationId);
        }
    }

    function removeItem(index) {
        document.getElementById(`item-${index}`).remove();
        calculateTotal();
    }

    function loadVariations(select, index, selectedVariationId = null) {
        const productId = select.value;
        const product = products.find(p => p.id == productId);
        const variationSelect = document.getElementById(`variation-${index}`);
        const priceInput = document.getElementById(`price-${index}`);

        variationSelect.innerHTML = '<option value="">Seleccionar...</option>';
        variationSelect.disabled = true;

        if (product) {
            if (!priceInput.value) {
                priceInput.value = product.price;
            }
            product.variations.forEach(v => {
                variationSelect.innerHTML += `<option value="${v.id}" ${v.id == selectedVariationId ? 'selected' : ''}>${v.color} - ${v.size} (Stock: ${v.stock})</option>`;
            });
            variationSelect.disabled = false;
        }
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        const rows = document.querySelectorAll('.item-row');
        
        rows.forEach(row => {
            const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
            total += quantity * price;
        });

        const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
        total += shipping;

        document.getElementById('total-display').innerText = '$' + total.toLocaleString('es-AR');
    }

    // Load existing items
    if (existingItems.length > 0) {
        existingItems.forEach(item => addItem(item));
    } else {
        addItem();
    }
</script>
@endsection
