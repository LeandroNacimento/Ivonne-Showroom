@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Nuevo Pedido</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Volver
        </a>
    </div>

    <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
        @csrf
        
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
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.clients.create') }}" class="text-xs text-brand-pink hover:underline mt-1 block">+ Crear Nuevo Cliente</a>
                    </div>

                    <div class="mb-4">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="datetime-local" name="date" id="date" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                            <option value="draft">Borrador</option>
                            <option value="reserved">Reservado</option>
                            <option value="paid">Pagado</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                </div>

                <!-- Payment & Shipping -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-4">
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                        <select name="payment_method" id="payment_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <option value="">Seleccionar...</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="mercadopago">Mercado Pago</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="delivery_type" class="block text-sm font-medium text-gray-700 mb-1">Entrega</label>
                        <select name="delivery_type" id="delivery_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <option value="showroom">Retiro en Showroom</option>
                            <option value="shipping">Envío</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="shipping_cost" class="block text-sm font-medium text-gray-700 mb-1">Costo de Envío</label>
                        <input type="number" name="shipping_cost" id="shipping_cost" value="0" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" onchange="calculateTotal()">
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                        <span>Total:</span>
                        <span id="total-display">$0</span>
                    </div>
                    <button type="submit" class="w-full mt-4 bg-brand-pink text-white px-6 py-3 rounded-md hover:bg-brand-heart transition-colors font-medium">
                        Guardar Pedido
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let itemCount = 0;

    function addItem() {
        const index = itemCount++;
        const html = `
            <div class="grid grid-cols-12 gap-4 items-end border-b border-gray-100 pb-4 item-row" id="item-${index}">
                <div class="col-span-4 relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>
                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm text-sm" 
                           placeholder="Buscar producto..." 
                           oninput="searchProduct(this, ${index})" 
                           onfocus="searchProduct(this, ${index})"
                           autocomplete="off">
                    <input type="hidden" name="items[${index}][product_id]" class="product-id-input">
                    
                    <!-- Results Dropdown -->
                    <div id="results-${index}" class="absolute z-10 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto hidden">
                        <!-- Results here -->
                    </div>
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Variación</label>
                    <select name="items[${index}][variation_id]" class="w-full rounded-md border-gray-300 shadow-sm text-sm" disabled id="variation-${index}">
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                    <input type="number" name="items[${index}][quantity]" value="1" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm" onchange="calculateTotal()">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                    <input type="number" name="items[${index}][unit_price]" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm text-sm" onchange="calculateTotal()" id="price-${index}">
                </div>
                <div class="col-span-1">
                    <button type="button" onclick="removeItem(${index})" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('items-container').insertAdjacentHTML('beforeend', html);
    }

    let searchTimeout;
    function searchProduct(input, index) {
        const query = input.value;
        const resultsDiv = document.getElementById(`results-${index}`);
        
        if (query.length < 2) {
            resultsDiv.classList.add('hidden');
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.products.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(products => {
                    resultsDiv.innerHTML = '';
                    if (products.length > 0) {
                        products.forEach(product => {
                            const div = document.createElement('div');
                            div.className = 'p-2 hover:bg-gray-100 cursor-pointer text-sm';
                            div.textContent = product.name;
                            div.onclick = () => selectProduct(product, index, input, resultsDiv);
                            resultsDiv.appendChild(div);
                        });
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.innerHTML = '<div class="p-2 text-gray-500 text-xs">No se encontraron productos</div>';
                        resultsDiv.classList.remove('hidden');
                    }
                });
        }, 300);
    }

    function selectProduct(product, index, input, resultsDiv) {
        input.value = product.name;
        document.querySelector(`#item-${index} .product-id-input`).value = product.id;
        resultsDiv.classList.add('hidden');
        
        // Update Price
        document.getElementById(`price-${index}`).value = product.price;

        // Update Variations
        const variationSelect = document.getElementById(`variation-${index}`);
        variationSelect.innerHTML = '<option value="">Seleccionar...</option>';
        
        if (product.variations && product.variations.length > 0) {
            product.variations.forEach(v => {
                variationSelect.innerHTML += `<option value="${v.id}">${v.color} - ${v.size} (Stock: ${v.stock})</option>`;
            });
            variationSelect.disabled = false;
        } else {
            variationSelect.disabled = true;
        }

        calculateTotal();
    }

    // Close results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('[id^="results-"]').forEach(el => el.classList.add('hidden'));
        }
    });

    function removeItem(index) {
        document.getElementById(`item-${index}`).remove();
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

    // Add first item by default
    addItem();
</script>
@endsection
