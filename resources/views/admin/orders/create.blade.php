@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Pedido</h1>
            <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>

        <div x-data="orderForm()">
            <form action="{{ route('admin.orders.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Order Details -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Items -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ítems del Pedido</h2>

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-12 gap-4 md:items-end border-b border-gray-100 pb-6 md:pb-4">
                                        <div class="md:col-span-4 md:pr-4 relative">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>

                                            <!-- En modo edición o tras seleccionar, mostramos la información elegida -->
                                            <div x-show="item.productId"
                                                class="flex items-center justify-between bg-gray-50 p-2 rounded-md border border-gray-200">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800"
                                                        x-text="item.productName"></span>
                                                </div>
                                                <button type="button" @click="clearProduct(index)"
                                                    class="text-xs text-brand-pink hover:underline">
                                                    Cambiar
                                                </button>

                                                <input type="hidden" :name="`items[${index}][product_id]`"
                                                    x-model="item.productId">
                                            </div>

                                            <!-- Si no hay producto seleccionado, mostramos el componente buscador Alpine -->
                                            <div x-show="!item.productId" class="relative">
                                                <input type="text"
                                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                    placeholder="Buscar producto..." x-model="item.productSearch"
                                                    @input.debounce.300ms="searchProduct(index)"
                                                    @focus="item.showResults = true" @click.away="item.showResults = false"
                                                    autocomplete="off">

                                                <!-- Results Dropdown -->
                                                <div x-show="item.showResults && item.searchResults.length > 0"
                                                    class="absolute z-50 left-0 min-w-full md:min-w-[450px] bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                                    <template x-for="result in item.searchResults" :key="result.id">
                                                        <div class="p-2 hover:bg-gray-100 cursor-pointer text-sm"
                                                            @click="selectProduct(index, result)" x-text="result.name">
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Variación</label>
                                            <select :name="`items[${index}][variation_id]`"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                x-model="item.variationId" :disabled="!item.productId"
                                                @change="updatePrice(index)">
                                                <option value="">Seleccionar...</option>
                                                <template x-for="variation in item.variations" :key="variation.id">
                                                    <option :value="variation.id"
                                                        x-text="`${variation.color !== 'N/A' ? variation.color + ' - ' : ''}${variation.size} (Stock: ${variation.stock})`">
                                                    </option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                                            <input type="number" :name="`items[${index}][quantity]`"
                                                x-model="item.quantity" min="1" :max="item.maxStock"
                                                @input="validateQuantity(index)"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                                            <input type="number" :name="`items[${index}][unit_price]`"
                                                x-model="item.unitPrice" step="0.01"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Subtotal</label>
                                            <div class="w-full text-sm font-semibold text-gray-800 mt-2 text-right md:text-left"
                                                x-text="formatCurrency((parseFloat(item.quantity) || 0) * (parseFloat(item.unitPrice) || 0))">
                                            </div>
                                        </div>
                                        <div class="md:col-span-1 mt-2 md:mt-0 flex md:justify-end">
                                            <button type="button" @click="removeItem(index)"
                                                class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addItem()"
                                class="mt-4 text-sm text-brand-pink hover:text-brand-heart font-medium">
                                + Agregar Producto
                            </button>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Client & Info -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="mb-4">
                                <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                                <select name="client_id" id="client_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    <option value="">Seleccionar Cliente</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('admin.clients.create') }}"
                                    class="text-xs text-brand-pink hover:underline mt-1 block">+ Crear Nuevo Cliente</a>
                            </div>

                            <div class="mb-4">
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                <input type="datetime-local" name="date" id="date"
                                    value="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    <option value="pendiente" selected>Pendiente</option>
                                    <option value="reservado">Reservado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment & Shipping -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="mb-4">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Método de
                                    Pago</label>
                                <select name="payment_method" id="payment_method"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                    <option value="">Seleccionar...</option>
                                    <option value="cash">Efectivo</option>
                                    <option value="transfer">Transferencia</option>
                                    <option value="mercadopago">Mercado Pago</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="delivery_type"
                                    class="block text-sm font-medium text-gray-700 mb-1">Entrega</label>
                                <select name="delivery_type" id="delivery_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                    <option value="showroom">Retiro en Showroom</option>
                                    <option value="shipping">Envío</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="shipping_cost" class="block text-sm font-medium text-gray-700 mb-1">Costo de
                                    Envío</label>
                                <input type="number" name="shipping_cost" x-model="shippingCost" min="0"
                                    step="0.01"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                                <span>Total:</span>
                                <span x-text="formatCurrency(calculateTotal())">$0</span>
                            </div>
                            <button type="submit"
                                class="w-full mt-4 bg-brand-pink text-white px-6 py-3 rounded-md hover:bg-brand-heart transition-colors font-medium">
                                Guardar Pedido
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <script>
            function orderForm() {
                return {
                    items: [{
                        productId: '',
                        productName: '',
                        productSearch: '',
                        variationId: '',
                        quantity: 1,
                        unitPrice: 0,
                        maxStock: null,
                        showResults: false,
                        searchResults: [],
                        variations: []
                    }],
                    shippingCost: 0,

                    addItem() {
                        this.items.push({
                            productId: '',
                            productName: '',
                            productSearch: '',
                            variationId: '',
                            quantity: 1,
                            unitPrice: 0,
                            maxStock: null,
                            showResults: false,
                            searchResults: [],
                            variations: []
                        });
                    },

                    removeItem(index) {
                        this.items.splice(index, 1);
                    },

                    clearProduct(index) {
                        let item = this.items[index];
                        item.productId = '';
                        item.productName = '';
                        item.productSearch = '';
                        item.variationId = '';
                        item.unitPrice = 0;
                        item.maxStock = null;
                        item.showResults = false;
                        item.searchResults = [];
                        item.variations = [];
                    },

                    searchProduct(index) {
                        let item = this.items[index];
                        if (item.productSearch.length < 2) {
                            item.searchResults = [];
                            return;
                        }

                        fetch(`{{ route('admin.products.search') }}?q=${encodeURIComponent(item.productSearch)}`)
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
                        item.productSearch = '';
                        item.unitPrice = product.price || 0;
                        item.maxStock = null;
                        item.variationId = '';
                        item.showResults = false;
                        item.searchResults = [];
                        item.variations = product.variations || [];
                    },

                    updatePrice(index) {
                        let item = this.items[index];
                        if (item.variationId && item.variations) {
                            let variation = item.variations.find(v => v.id == item.variationId);
                            if (variation) {
                                item.unitPrice = variation.price;
                                item.maxStock = variation.stock;

                                this.validateQuantity(index);
                            }
                        }
                    },

                    validateQuantity(index) {
                        let item = this.items[index];
                        if (item.maxStock !== null && item.quantity > item.maxStock) {
                            item.quantity = item.maxStock;
                        }
                        if (item.quantity !== '' && item.quantity < 1) {
                            item.quantity = 1;
                        }
                    },

                    calculateTotal() {
                        let itemsTotal = this.items.reduce((sum, item) => {
                            let q = parseFloat(item.quantity) || 0;
                            let p = parseFloat(item.unitPrice) || 0;
                            return sum + (q * p);
                        }, 0);
                        let shipping = parseFloat(this.shippingCost) || 0;
                        return itemsTotal + shipping;
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('es-AR', {
                            style: 'currency',
                            currency: 'ARS'
                        }).format(value);
                    }
                }
            }
        </script>
        </script>
    @endsection
