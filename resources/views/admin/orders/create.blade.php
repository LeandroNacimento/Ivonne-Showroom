@extends('layouts.admin')

@section('content')
    @php
        $pendingStatus = \App\Models\Order::STATUS_PENDING;
        $reservedStatus = \App\Models\Order::STATUS_RESERVED;
    @endphp

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Pedido</h1>
            <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>

        <script>
            window.ORDER_ENDPOINTS = {
                searchProducts: "{{ route('admin.products.search') }}",
                searchClients: "{{ route('admin.clients.search') }}"
            };

            window.INITIAL_ORDER_DATA = {
                oldItems: {!! json_encode(old('items')) !!},
                errors: {!! json_encode($errors->toArray()) !!},
                deliveryType: @json(old('delivery_type', 'showroom')),
                shippingCost: {!! json_encode(old('shipping_cost') ?: 0) !!},
                freeShipping: {{ old('delivery_type') === 'shipping' && (old('shipping_cost') == 0 && old('shipping_cost') !== null) ? 'true' : 'false' }},
                clientMode: @json(old('new_client_name') ? 'new' : 'existing'),
                clientId: @json(old('client_id')),
                clientSearch: '' // Cannot repopulate client name easily on error without another DB query, manual search required
            };
        </script>
        <div x-data="orderForm(window.INITIAL_ORDER_DATA)">
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
                                        class="grid grid-cols-1 gap-4 border-b border-gray-100 pb-6 md:grid-cols-[repeat(13,minmax(0,1fr))] md:items-start md:gap-x-4 md:gap-y-3 md:pb-4">
                                        <div class="md:col-span-4 md:pr-4 relative">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>

                                            <!-- Input oculto para enviar el ID del producto -->
                                            <input type="hidden" :name="`items[${index}][product_id]`"
                                                x-model="item.productId">

                                            <div class="relative">
                                                <input type="text"
                                                    class="h-10 w-full truncate rounded-md shadow-sm text-sm"
                                                    :class="getError(`items.${index}.product_id`) ? 'border-red-500' :
                                                        'border-gray-300'"
                                                    placeholder="Buscar producto..."
                                                    :title="item.productSearch || item.productName || ''"
                                                    x-model="item.productSearch"
                                                    @input="handleProductInput(index); clearError(`items.${index}.product_id`)"
                                                    @focus="item.showResults = !item.productId || item.productSearch !== item.productName"
                                                    @click.away="item.showResults = false"
                                                    autocomplete="off">

                                                <template x-if="getError(`items.${index}.product_id`)">
                                                    <div class="text-[10px] text-red-500 mt-1"
                                                        x-text="getError(`items.${index}.product_id`)"></div>
                                                </template>

                                                <!-- Results Dropdown -->
                                                <div x-show="item.showResults"
                                                    class="absolute z-50 left-0 min-w-full md:min-w-[450px] bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1">

                                                    <!-- Loading state -->
                                                    <div x-show="item.isSearching"
                                                        class="p-3 text-sm text-gray-500 text-center">
                                                        Buscando...
                                                    </div>

                                                    <!-- No results -->
                                                    <div x-cloak
                                                        x-show="item.searchResults.length === 0 && item.hasSearched && !item.isSearching"
                                                        class="p-3 text-sm text-gray-500 text-center">
                                                        Producto no encontrado
                                                    </div>

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
                                                class="h-10 w-full rounded-md shadow-sm text-sm"
                                                :class="getError(`items.${index}.variation_id`) ? 'border-red-500' :
                                                    'border-gray-300'"
                                                x-model="item.variationId" :disabled="!item.productId"
                                                @change="updatePrice(index); clearError(`items.${index}.variation_id`)">
                                                <option value="">Seleccionar...</option>
                                                <template x-for="variation in item.variations" :key="variation.id">
                                                    <option :value="variation.id"
                                                        x-text="`${variation.color !== 'N/A' ? variation.color : ''}${variation.color !== 'N/A' && variation.size !== 'ÚNICO' ? ' - ' : ''}${variation.size !== 'ÚNICO' ? variation.size : ''} (Stock: ${variation.stock})`">
                                                    </option>
                                                </template>
                                            </select>
                                            <template x-if="getError(`items.${index}.variation_id`)">
                                                <div class="text-[10px] text-red-500 mt-1"
                                                    x-text="getError(`items.${index}.variation_id`)"></div>
                                            </template>
                                        </div>
                                        <div class="md:col-span-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Cant.</label>
                                            <input type="number" :name="`items[${index}][quantity]`"
                                                x-model="item.quantity" min="1" :max="item.maxStock"
                                                @input="validateQuantity(index); clearError(`items.${index}.quantity`)"
                                                class="h-10 w-full rounded-md shadow-sm text-sm"
                                                :class="getError(`items.${index}.quantity`) ? 'border-red-500' :
                                                    'border-gray-300'">
                                            <template x-if="getError(`items.${index}.quantity`)">
                                                <div class="text-[10px] text-red-500 mt-1"
                                                    x-text="getError(`items.${index}.quantity`)"></div>
                                            </template>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Precio
                                                Unit.</label>
                                            <div
                                                class="flex h-10 w-full items-center rounded-md border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700">
                                                <span
                                                    x-text="item.unitPrice ? formatCurrency(item.unitPrice) : 'Seleccionar variacion'"></span>
                                            </div>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Subtotal</label>
                                            <div
                                                class="flex h-10 w-full items-center justify-end rounded-md border border-gray-100 bg-gray-50 px-3 text-sm font-semibold text-gray-800 md:justify-start"
                                                x-text="formatCurrency((parseFloat(item.quantity) || 0) * (parseFloat(item.unitPrice) || 0))">
                                            </div>
                                        </div>
                                        <div class="mt-2 flex md:col-span-1 md:mt-0 md:justify-center md:pt-6">
                                            <button type="button" @click="removeItem(index)"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-red-500 transition-colors hover:bg-red-50 hover:text-red-700">
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
                                <div x-show="clientMode === 'existing'" class="relative">
                                    <input type="hidden" name="client_id" x-model="clientId">
                                    
                                    <input
                                        type="text"
                                        placeholder="Buscar cliente..."
                                        x-model="clientSearch"
                                        @input.debounce.300ms="searchClient"
                                        @focus="showClientResults = true"
                                        @click.outside="showClientResults = false"
                                        class="w-full rounded-md border-[{{ $errors->has('client_id') ? 'red-500' : 'gray-300' }}] shadow-sm text-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                    >

                                    <!-- Results dropdown -->
                                    <div x-show="showClientResults"
                                         class="absolute z-10 bg-white border border-gray-200 w-full mt-1 rounded-md shadow-lg max-h-60 overflow-y-auto">

                                        <!-- Loading state -->
                                        <div x-show="isSearchingClient" class="p-3 text-sm text-gray-500 text-center">
                                            Buscando...
                                        </div>

                                        <template x-if="clientResults.length === 0 && !isSearchingClient && clientSearch.length > 0">
                                            <div class="p-3 text-gray-500 text-sm text-center">
                                                No se encontraron clientes
                                            </div>
                                        </template>

                                        <template x-for="client in clientResults" :key="client.id">
                                            <div
                                                @click="selectClient(client)"
                                                class="p-2 hover:bg-gray-100 cursor-pointer text-sm"
                                                x-text="client.name"
                                            ></div>
                                        </template>
                                    </div>
                                    @error('client_id')
                                        <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nuevo Cliente -->
                                <div x-cloak x-show="clientMode === 'new'" x-ref="newClientFields"
                                    class="space-y-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre Completo
                                            *</label>
                                        <input type="text" name="new_client_name" id="new_client_name"
                                            value="{{ old('new_client_name') }}"
                                            class="w-full rounded-md border-[{{ $errors->has('new_client_name') ? 'red-500' : 'gray-300' }}] shadow-sm text-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                            :required="clientMode === 'new'">
                                        @error('new_client_name')
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Teléfono</label>
                                        <input type="text" name="new_client_phone" id="new_client_phone"
                                            value="{{ old('new_client_phone') }}"
                                            class="w-full rounded-md border-[{{ $errors->has('new_client_phone') ? 'red-500' : 'gray-300' }}] shadow-sm text-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50">
                                        @error('new_client_phone')
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Instagram (@)</label>
                                        <input type="text" name="new_client_instagram" id="new_client_instagram"
                                            value="{{ old('new_client_instagram') }}"
                                            class="w-full rounded-md border-[{{ $errors->has('new_client_instagram') ? 'red-500' : 'gray-300' }}] shadow-sm text-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50">
                                        @error('new_client_instagram')
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="new_client_email" id="new_client_email"
                                            value="{{ old('new_client_email') }}"
                                            class="w-full rounded-md border-[{{ $errors->has('new_client_email') ? 'red-500' : 'gray-300' }}] shadow-sm text-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50">
                                        @error('new_client_email')
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Notas</label>
                                        <textarea name="new_client_notes" id="new_client_notes" rows="2"
                                            class="w-full rounded-md border-[{{ $errors->has('new_client_notes') ? 'red-500' : 'gray-300' }}] shadow-sm text-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50">{{ old('new_client_notes') }}</textarea>
                                        @error('new_client_notes')
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                <input type="datetime-local" name="date" id="date"
                                    value="{{ old('date', now()->format('Y-m-d\TH:i')) }}"
                                    max="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-md border-[{{ $errors->has('date') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                @error('date')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" id="status"
                                    class="w-full rounded-md border-[{{ $errors->has('status') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    <option value="{{ $pendingStatus }}"
                                        {{ old('status', $pendingStatus) === $pendingStatus ? 'selected' : '' }}>Pendiente
                                    </option>
                                    <option value="{{ $reservedStatus }}" {{ old('status') === $reservedStatus ? 'selected' : '' }}>
                                        Reservado</option>
                                </select>
                                @error('status')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Payment & Shipping -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="mb-4">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Método de
                                    Pago</label>
                                <select name="payment_method" id="payment_method"
                                    class="w-full rounded-md border-[{{ $errors->has('payment_method') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    <option value="">Seleccionar...</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                        Efectivo</option>
                                    <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>
                                        Transferencia</option>
                                    <option value="mercadopago"
                                        {{ old('payment_method') == 'mercadopago' ? 'selected' : '' }}>Mercado Pago
                                    </option>
                                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Otro
                                    </option>
                                </select>
                                @error('payment_method')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="delivery_type"
                                    class="block text-sm font-medium text-gray-700 mb-1">Entrega</label>
                                <select name="delivery_type" id="delivery_type" x-model="deliveryType"
                                    class="w-full rounded-md border-[{{ $errors->has('delivery_type') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    <option value="showroom" {{ old('delivery_type') == 'showroom' ? 'selected' : '' }}>
                                        Retiro en Showroom</option>
                                    <option value="shipping" {{ old('delivery_type') == 'shipping' ? 'selected' : '' }}>
                                        Envío</option>
                                </select>
                                @error('delivery_type')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4" x-cloak x-show="deliveryType === 'shipping'">
                                <label for="shipping_cost" class="block text-sm font-medium text-gray-700 mb-1">Costo de
                                    Envío</label>
                                <input type="number" name="shipping_cost" x-model="shippingCost" min="0"
                                    step="0.01" :readonly="freeShipping"
                                    :required="deliveryType === 'shipping' && !freeShipping"
                                    class="w-full rounded-md border-[{{ $errors->has('shipping_cost') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 disabled:bg-gray-100 disabled:text-gray-500">

                                <div class="mt-2 flex items-center">
                                    <input type="checkbox" id="free_shipping" x-model="freeShipping"
                                        class="rounded border-gray-300 text-brand-pink focus:ring-brand-pink"
                                        @change="if(freeShipping) shippingCost = 0">
                                    <label for="free_shipping" class="ml-2 text-sm text-gray-600">No se cobró
                                        envío</label>
                                </div>
                                @error('shipping_cost')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                                <span>Total:</span>
                                <span x-text="formatCurrency(total)">$0</span>
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

    </div>
    </div>
@endsection
