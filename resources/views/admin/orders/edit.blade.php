@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto">
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

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Order Details -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Items -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold text-gray-800">Ítems del Pedido</h2>
                                @if ($order->status === 'reservado')
                                    <span
                                        class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-2 py-1">
                                        🔒 Ítems bloqueados (pedido reservado)
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-12 gap-4 md:items-end border-b border-gray-100 pb-6 md:pb-4">
                                        <div class="md:col-span-4 md:pr-4 relative">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>
                                            <input type="text"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm {{ $order->status === 'reservado' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                placeholder="Buscar producto..." x-model="item.productName"
                                                @if ($order->status !== 'reservado') @input.debounce.300ms="searchProduct(index)"
                                                @focus="item.showResults = true" @click.away="item.showResults = false" @endif
                                                autocomplete="off" {{ $order->status === 'reservado' ? 'readonly' : '' }}>
                                            <input type="hidden" :name="`items[${index}][product_id]`"
                                                x-model="item.productId">

                                            <!-- Results Dropdown -->
                                            <div x-show="item.showResults && item.searchResults.length > 0"
                                                class="absolute z-50 left-0 min-w-full md:min-w-[450px] bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                                <template x-for="result in item.searchResults" :key="result.id">
                                                    <div class="p-2 hover:bg-gray-100 cursor-pointer text-sm"
                                                        @click="selectProduct(index, result)" x-text="result.name"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Variación</label>
                                            <select :name="`items[${index}][variation_id]`"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                x-model="item.variationId" @change="updatePrice(index)"
                                                :disabled="!item.productId ||
                                                    {{ $order->status === 'reservado' ? 'true' : 'false' }}">
                                                <option value="">Seleccionar...</option>
                                                <template x-for="variation in item.variations" :key="variation.id">
                                                    <option :value="variation.id"
                                                        x-text="`${variation.color} - ${variation.size} (Stock: ${variation.stock})`">
                                                    </option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                                            <input type="number" :name="`items[${index}][quantity]`"
                                                x-model="item.quantity" min="1" :max="item.maxStock"
                                                @input="validateQuantity(index)"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm {{ $order->status === 'reservado' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ $order->status === 'reservado' ? 'readonly' : '' }}>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                                            <input type="number" :name="`items[${index}][unit_price]`"
                                                x-model="item.unitPrice" step="0.01"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm {{ $order->status === 'reservado' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                {{ $order->status === 'reservado' ? 'readonly' : '' }}>
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Subtotal</label>
                                            <div class="w-full text-sm font-semibold text-gray-800 mt-2 text-right md:text-left"
                                                x-text="formatCurrency((parseFloat(item.quantity) || 0) * (parseFloat(item.unitPrice) || 0))">
                                            </div>
                                        </div>
                                        <div class="md:col-span-1 mt-2 md:mt-0 flex md:justify-end">
                                            @if ($order->status !== 'reservado')
                                                <button type="button" @click="removeItem(index)"
                                                    class="text-red-500 hover:text-red-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </template>
                            </div>

                            @if ($order->status !== 'reservado')
                                <button type="button" @click="addItem()"
                                    class="mt-4 text-sm text-brand-pink hover:text-brand-heart font-medium">
                                    + Agregar Producto
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Client & Info -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="mb-4" x-data="{ clientMode: '{{ old('new_client_name') ? 'new' : 'existing' }}' }">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>

                                <div class="flex items-center space-x-4 mb-4">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" class="text-brand-pink focus:ring-brand-pink"
                                            x-model="clientMode" value="existing"
                                            @change="$refs.newClientFields.querySelectorAll('input, textarea').forEach(i => i.value = '')">
                                        <span class="ml-2 text-sm text-gray-700">Existente</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" class="text-brand-pink focus:ring-brand-pink"
                                            x-model="clientMode" value="new" @change="$refs.clientId.value = ''">
                                        <span class="ml-2 text-sm text-gray-700">Nuevo Cliente</span>
                                    </label>
                                </div>

                                <!-- Cliente Existente -->
                                <div x-show="clientMode === 'existing'">
                                    <select name="client_id" id="client_id" x-ref="clientId"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                        :required="clientMode === 'existing'">
                                        <option value="">Seleccionar Cliente</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ (old('client_id') ?? $order->client_id) == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Nuevo Cliente -->
                                <div x-cloak x-show="clientMode === 'new'" x-ref="newClientFields"
                                    class="space-y-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre Completo
                                            *</label>
                                        <input type="text" name="new_client_name" id="new_client_name"
                                            value="{{ old('new_client_name') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                            :required="clientMode === 'new'">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Teléfono</label>
                                        <input type="text" name="new_client_phone" id="new_client_phone"
                                            value="{{ old('new_client_phone') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Instagram (@)</label>
                                        <input type="text" name="new_client_instagram" id="new_client_instagram"
                                            value="{{ old('new_client_instagram') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="new_client_email" id="new_client_email"
                                            value="{{ old('new_client_email') }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Notas</label>
                                        <textarea name="new_client_notes" id="new_client_notes" rows="2"
                                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">{{ old('new_client_notes') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                <input type="datetime-local" name="date" id="date"
                                    value="{{ $order->date->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    @if ($order->status === 'pendiente')
                                        <option value="pendiente" selected>Pendiente</option>
                                        <option value="reservado">Reservado</option>
                                        <option value="cancelado">Cancelado</option>
                                    @elseif($order->status === 'reservado')
                                        <option value="reservado" selected>Reservado</option>
                                        <option value="entregado">Entregado</option>
                                        <option value="cancelado">Cancelado</option>
                                    @else
                                        <option value="{{ $order->status }}" selected>{{ ucfirst($order->status) }}
                                        </option>
                                    @endif
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
                                    <option value="cash" {{ $order->payment_method == 'cash' ? 'selected' : '' }}>
                                        Efectivo</option>
                                    <option value="transfer" {{ $order->payment_method == 'transfer' ? 'selected' : '' }}>
                                        Transferencia</option>
                                    <option value="mercadopago"
                                        {{ $order->payment_method == 'mercadopago' ? 'selected' : '' }}>Mercado Pago
                                    </option>
                                    <option value="other" {{ $order->payment_method == 'other' ? 'selected' : '' }}>Otro
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="delivery_type"
                                    class="block text-sm font-medium text-gray-700 mb-1">Entrega</label>
                                <select name="delivery_type" id="delivery_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                    <option value="showroom" {{ $order->delivery_type == 'showroom' ? 'selected' : '' }}>
                                        Retiro en Showroom</option>
                                    <option value="shipping" {{ $order->delivery_type == 'shipping' ? 'selected' : '' }}>
                                        Envío</option>
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
                                    productName: item.product ? item.product.name :
                                    'Producto Eliminado', // Fallback
                                    variationId: item.variation_id,
                                    quantity: item.quantity,
                                    unitPrice: item.unit_price,
                                    maxStock: null,
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
                            maxStock: null,
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
                        item.maxStock = null;
                        item.variations = product.variations || [];
                        item.showResults = false;
                        item.variationId = '';
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

                    // New helper to correct hydration issue
                    loadVariationsForItem(index, productId) {
                        fetch(`{{ route('admin.products.search') }}?q=${productId}`)
                            .then(res => res.json())
                            .then(products => {
                                const product = products.find(p => p.id == productId);
                                if (product) {
                                    let item = this.items[index];
                                    item.variations = product.variations || [];

                                    if (item.variationId) {
                                        let variation = item.variations.find(v => v.id == item.variationId);
                                        if (variation) {
                                            item.maxStock = variation.stock;
                                        }
                                    }
                                }
                            });
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
