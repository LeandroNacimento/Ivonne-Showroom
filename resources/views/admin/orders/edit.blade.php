@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Pedido #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Volver
        </a>
    </div>

    <div x-data="orderForm()">
        <form action="{{ route('admin.orders.update', $order) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Details -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Items -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Ítems del Pedido</h2>
                        
                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-4 items-end border-b border-gray-100 pb-4">
                                    <div class="col-span-4 relative">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>
                                        <input type="text" class="w-full rounded-md border-gray-300 shadow-sm text-sm" 
                                               placeholder="Buscar producto..." 
                                               x-model="item.productName"
                                               @input.debounce.300ms="searchProduct(index)"
                                               @focus="item.showResults = true"
                                               @click.away="item.showResults = false"
                                               autocomplete="off">
                                        <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.productId">
                                        
                                        <!-- Results Dropdown -->
                                        <div x-show="item.showResults && item.searchResults.length > 0" 
                                             class="absolute z-10 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                            <template x-for="result in item.searchResults" :key="result.id">
                                                <div class="p-2 hover:bg-gray-100 cursor-pointer text-sm" 
                                                     @click="selectProduct(index, result)" 
                                                     x-text="result.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-span-3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Variación</label>
                                        <select :name="`items[${index}][variation_id]`" 
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm" 
                                                x-model="item.variationId"
                                                :disabled="!item.productId">
                                            <option value="">Seleccionar...</option>
                                            <template x-for="variation in item.variations" :key="variation.id">
                                                <option :value="variation.id" x-text="`${variation.color} - ${variation.size} (Stock: ${variation.stock})`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                                        <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                                        <input type="number" :name="`items[${index}][unit_price]`" x-model="item.unitPrice" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="col-span-1">
                                        <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addItem()" class="mt-4 text-sm text-brand-pink hover:text-brand-heart font-medium">
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
                            <a href="{{ route('admin.clients.create') }}" class="text-xs text-brand-pink hover:underline mt-1 block">+ Crear Nuevo Cliente</a>
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
                            <input type="number" name="shipping_cost" x-model="shippingCost" min="0" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                            <span>Total:</span>
                            <span x-text="formatCurrency(calculateTotal())">$0</span>
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
    function orderForm() {
        return {
            items: [],
            shippingCost: {{ $order->shipping_cost ?? 0 }},

            init() {
                const existing = @json($order->items);
                if (existing.length > 0) {
                    existing.forEach(item => {
                        this.items.push({
                            productId: item.product_id,
                            productName: item.product ? item.product.name : 'Producto Eliminado', // Fallback
                            variationId: item.variation_id,
                            quantity: item.quantity,
                            unitPrice: item.unit_price,
                            showResults: false,
                            searchResults: [],
                            variations: [] // Will be loaded
                        });
                        
                        // Hydrate variations
                        this.loadVariationsForItem(this.items.length - 1, item.product_id);
                    });
                } else {
                    this.addItem();
                }
            },

            addItem() {
                this.items.push({
                    productId: '',
                    productName: '',
                    variationId: '',
                    quantity: 1,
                    unitPrice: 0,
                    showResults: false,
                    searchResults: [],
                    variations: []
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            searchProduct(index) {
                let item = this.items[index];
                if (item.productName.length < 2) {
                    item.searchResults = [];
                    return;
                }

                fetch(`{{ route('admin.products.search') }}?q=${encodeURIComponent(item.productName)}`)
                    .then(res => res.json())
                    .then(data => {
                        item.searchResults = data;
                        item.showResults = true;
                    });
            },

            selectProduct(index, product) {
                let item = this.items[index];
                item.productId = product.id;
                item.productName = product.name;
                item.unitPrice = product.price;
                item.variations = product.variations || [];
                item.showResults = false;
            },

            // New helper to correct hydration issue
            loadVariationsForItem(index, productId) {
                fetch(`{{ route('admin.products.search') }}?q=${productId}`)
                    .then(res => res.json())
                    .then(products => {
                        const product = products.find(p => p.id == productId);
                        if (product) {
                            this.items[index].variations = product.variations || [];
                            // Ensure price is set if missing (though unlikely in edit)
                           // this.items[index].unitPrice = product.price; 
                        }
                    });
            },

            calculateTotal() {
                let itemsTotal = this.items.reduce((sum, item) => {
                    return sum + (Number(item.quantity) * Number(item.unitPrice));
                }, 0);
                return itemsTotal + Number(this.shippingCost);
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value);
            }
        }
    }
</script>
