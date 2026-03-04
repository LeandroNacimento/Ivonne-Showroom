@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Pedido</h1>
            <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>

        <div x-data="orderForm()">
            <form action="{{ route('admin.orders.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Order Details -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Items -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ítems del Pedido</h2>

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-4 items-end border-b border-gray-100 pb-4">
                                        <div class="col-span-7 relative">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Producto /
                                                Variación</label>

                                            <!-- En modo edición o tras seleccionar, mostramos la información elegida -->
                                            <div x-show="item.variationId"
                                                class="flex items-center justify-between bg-gray-50 p-2 rounded-md border border-gray-200">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800"
                                                        x-text="item.productName"></span>
                                                    <span class="text-xs text-gray-500" x-text="item.variationLabel"></span>
                                                </div>
                                                <button type="button" @click="clearProduct(index)"
                                                    class="text-xs text-brand-pink hover:underline">
                                                    Cambiar
                                                </button>

                                                <input type="hidden" :name="`items[${index}][product_id]`"
                                                    x-model="item.productId">
                                                <input type="hidden" :name="`items[${index}][variation_id]`"
                                                    x-model="item.variationId">
                                            </div>

                                            <!-- Si no hay variación seleccionada, mostramos el componente Livewire buscador interactivo -->
                                            <div x-show="!item.variationId">
                                                @livewire('admin.orders.order-product-selector', ['index' => '{{ $index }}'], key(str()->random(10)))
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                                            <input type="number" :name="`items[${index}][quantity]`"
                                                x-model="item.quantity" min="1"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                                            <input type="number" :name="`items[${index}][unit_price]`"
                                                x-model="item.unitPrice" step="0.01"
                                                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div class="col-span-1">
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
                    <div class="space-y-6">
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
                        variationId: '',
                        variationLabel: '',
                        quantity: 1,
                        unitPrice: 0
                    }],
                    shippingCost: 0,

                    addItem() {
                        this.items.push({
                            productId: '',
                            productName: '',
                            variationId: '',
                            variationLabel: '',
                            quantity: 1,
                            unitPrice: 0
                        });
                    },

                    removeItem(index) {
                        this.items.splice(index, 1);
                    },

                    clearProduct(index) {
                        let item = this.items[index];
                        item.productId = '';
                        item.productName = '';
                        item.variationId = '';
                        item.variationLabel = '';
                        item.unitPrice = 0;
                    },

                    listenLivewireEvents() {
                        window.addEventListener('productSelected', event => {
                            const {
                                index,
                                product,
                                variation,
                                price
                            } = event.detail[0];
                            let item = this.items[index];

                            if (item) {
                                item.productId = product.id;
                                item.productName = product.name;
                                item.variationId = variation.id;
                                item.variationLabel = `Color: ${variation.color} | Talle: ${variation.size}`;
                                item.unitPrice = price;
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
                        return new Intl.NumberFormat('es-AR', {
                            style: 'currency',
                            currency: 'ARS'
                        }).format(value);
                    }
                }
            }
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('orderForm', () => orderForm());
            });
        </script>
    @endsection
