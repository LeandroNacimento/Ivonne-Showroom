@extends('layouts.admin')

@section('content')
    @php
        $reservedStatus = \App\Models\Order::STATUS_RESERVED;
    @endphp

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Editar Pedido #{{ $order->id }}</h1>
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
                existingItems: {!! json_encode($existingItems) !!},
                errors: {!! json_encode($errors->toArray()) !!},
                status: @json(old('status', $order->status)),
                deliveryType: @json(old('delivery_type', $order->delivery_type ?? 'showroom')),
                shippingCost: {!! json_encode(old('shipping_cost', $order->shipping_cost) ?: 0) !!},
                freeShipping: {{ old('delivery_type', $order->delivery_type ?? '') === 'shipping' && (old('shipping_cost', $order->shipping_cost) == 0 && old('shipping_cost', $order->shipping_cost) !== null) ? 'true' : 'false' }},
                clientMode: @json(old('new_client_name') ? 'new' : 'existing'),
                clientId: {{ old('client_id', $order->client_id ?? 'null') }},
                clientSearch: {!! json_encode(old('client_name_display', $order->client ? $order->client->name : '')) !!}
            };
        </script>
        <div x-data="orderForm(window.INITIAL_ORDER_DATA)">
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Order Details -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Items -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-base font-semibold text-gray-900 mb-4">Productos del Pedido</h2>

                                <!-- Banner global de errores -->
                                <template x-if="Object.keys(errors).length > 0">
                                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">No se pudo guardar el pedido</h3>
                                                <p class="mt-1 text-sm text-red-700">Revisá los errores resaltados más abajo antes de volver a intentar.</p>
                                                
                                                <!-- Error global de validación de items (ej. variaciones duplicadas) -->
                                                <template x-if="getError('items')">
                                                    <div class="mt-2 text-sm font-bold text-red-800" x-text="getError('items')"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                @if ($order->status === $reservedStatus)
                                    <span
                                        class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-2 py-1">
                                        🔒 Ítems bloqueados (pedido reservado)
                                    </span>
                                @endif
                            </div>


                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex flex-col gap-3 border-b border-gray-100 pb-6 pt-2 md:grid md:grid-cols-13 md:items-start md:gap-x-4 md:gap-y-3 md:pb-4 md:pt-0">
                                        <div class="md:col-span-4 md:pr-4 relative">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 md:text-xs md:font-medium md:text-gray-500">Producto</label>
                                            <input type="hidden" :name="`items[${index}][product_id]`"
                                                x-model="item.productId">

                                            <div class="relative">
                                                <input type="text"
                                                    class="h-10 w-full truncate rounded-md shadow-sm text-sm {{ $order->status === $reservedStatus ? 'bg-gray-100 cursor-not-allowed opacity-80' : '' }}"
                                                    :class="getError(`items.${index}.product_id`) ? 'border-red-500' :
                                                        'border-gray-300'"
                                                    placeholder="Buscar producto..."
                                                    :title="item.productSearch || item.productName || ''"
                                                    x-model="item.productSearch"
                                                    @input="handleProductInput(index); clearError(`items.${index}.product_id`)"
                                                    @focus="item.showResults = !item.productId || item.productSearch !== item.productName"
                                                    @click.away="item.showResults = false"
                                                    autocomplete="off"
                                                    {{ $order->status === $reservedStatus ? 'readonly' : '' }}>

                                                <template x-if="getError(`items.${index}.product_id`)">
                                                    <div class="text-[10px] text-red-500 mt-1"
                                                        x-text="getError(`items.${index}.product_id`)"></div>
                                                </template>

                                                <!-- Results Dropdown -->
                                                <div x-show="item.showResults"
                                                    class="absolute z-50 left-0 min-w-full md:min-w-112.5 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1">

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
                                                            @click="selectProduct(index, result)" x-text="result.name"></div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 md:text-xs md:font-medium md:text-gray-500">Variación</label>
                                            <select :name="`items[${index}][variation_id]`"
                                                class="h-10 w-full rounded-md shadow-sm text-sm {{ $order->status === $reservedStatus ? 'bg-gray-100 cursor-not-allowed pointer-events-none opacity-80' : '' }}"
                                                :class="getError(`items.${index}.variation_id`) ? 'border-red-500' :
                                                    'border-gray-300'"
                                                x-model="item.variationId"
                                                x-effect="syncVariationSelect($el, item)"
                                                @change="updatePrice(index); clearError(`items.${index}.variation_id`)"
                                                :disabled="!item.productId">
                                                <option value="">Seleccionar...</option>
                                                <template x-for="variation in item.variations" :key="variation.id">
                                                    <option :value="String(variation.id)"
                                                        x-text="`${variation.color !== 'N/A' ? variation.color : ''}${variation.color !== 'N/A' && variation.size !== 'ÚNICO' ? ' - ' : ''}${variation.size !== 'ÚNICO' ? variation.size : ''} (Stock: ${variation.stock})`">
                                                    </option>
                                                </template>
                                            </select>
                                            <template x-if="getError(`items.${index}.variation_id`)">
                                                <div class="text-[10px] text-red-500 mt-1"
                                                    x-text="getError(`items.${index}.variation_id`)"></div>
                                            </template>
                                            <template x-if="item.initialVariationOption?.missing">
                                                <div class="text-[10px] text-amber-600 mt-1">
                                                    La variación original del pedido ya no existe. Revisa antes de guardar.
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Contenedor mobile para Cantidad y Totales -->
                                        <div class="flex items-center justify-between bg-gray-50/80 p-3 rounded-lg border border-gray-100 mt-1 md:contents">
                                            <div class="w-24 md:w-auto md:col-span-1">
                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 md:text-xs md:font-medium md:text-gray-500">Cant.</label>
                                                <input type="number" :name="`items[${index}][quantity]`"
                                                    x-model="item.quantity" min="1" :max="item.maxStock"
                                                    @input="validateQuantity(index); clearError(`items.${index}.quantity`)"
                                                    class="h-10 w-full rounded-md shadow-sm text-sm font-semibold text-center md:text-left {{ $order->status === $reservedStatus ? 'bg-gray-100 cursor-not-allowed pointer-events-none opacity-80' : '' }}"
                                                    :class="getError(`items.${index}.quantity`) ? 'border-red-500 bg-red-50' :
                                                        'border-gray-300'"
                                                    {{ $order->status === $reservedStatus ? 'readonly' : '' }}>
                                            </div>

                                            <!-- Precios mobile -->
                                            <div class="text-right md:hidden">
                                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Subtotal</div>
                                                <div class="text-xl font-black text-gray-900 leading-none {{ $order->status === $reservedStatus ? 'opacity-80' : '' }}"
                                                    x-text="formatCurrency((parseFloat(item.quantity) || 0) * (parseFloat(item.unitPrice) || 0))">
                                                </div>
                                                <div class="text-[11px] font-medium text-gray-500 mt-1"
                                                    x-text="item.unitPrice ? formatCurrency(item.unitPrice) + ' c/u' : 'Seleccionar variación'">
                                                </div>
                                            </div>

                                            <!-- Precios desktop -->
                                            <div class="hidden md:block md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit.</label>
                                                <div class="flex h-10 w-full items-center rounded-md border border-gray-200 px-3 text-sm text-gray-700 {{ $order->status === $reservedStatus ? 'bg-gray-100 opacity-80' : 'bg-gray-50' }}">
                                                    <span x-text="item.unitPrice ? formatCurrency(item.unitPrice) : 'Seleccionar variacion'"></span>
                                                </div>
                                            </div>
                                            <div class="hidden md:block md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Subtotal</label>
                                                <div class="flex h-10 w-full items-center justify-end rounded-md border border-gray-100 bg-gray-50 px-3 text-sm font-semibold text-gray-800 md:justify-start {{ $order->status === $reservedStatus ? 'opacity-80' : '' }}"
                                                    x-text="formatCurrency((parseFloat(item.quantity) || 0) * (parseFloat(item.unitPrice) || 0))">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Acciones -->
                                        <div class="flex items-center justify-between mt-1 md:col-span-1 md:mt-0 md:justify-center md:pt-6">
                                            @if ($order->status !== $reservedStatus)
                                                <button type="button" @click="removeItem(index)"
                                                    class="inline-flex items-center text-sm font-medium text-red-500 hover:text-red-700 transition-colors md:h-10 md:w-10 md:justify-center md:rounded-md md:hover:bg-red-50">
                                                    <svg class="w-4 h-4 mr-1 md:mr-0 md:w-5 md:h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    <span class="md:hidden">Eliminar</span>
                                                </button>
                                                
                                                <!-- Botón Mismo Producto Mobile -->
                                                <button type="button" @click="addSameProduct(index)" x-show="item.productId"
                                                    class="inline-flex items-center text-sm font-semibold text-brand-pink hover:text-brand-heart transition-colors md:hidden">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                    Mismo producto
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <!-- Botón Mismo Producto Desktop -->
                                        @if ($order->status !== $reservedStatus)
                                            <div class="hidden md:block md:col-span-13 text-right -mt-2">
                                                <button type="button" @click="addSameProduct(index)" x-show="item.productId"
                                                    class="inline-flex items-center text-xs font-semibold text-brand-pink hover:text-brand-heart transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                    Agregar mismo producto
                                                </button>
                                            </div>
                                        @endif

                                        <!-- Error local visible para la card (Stock / Cantidad) -->
                                        <template x-if="getError(`items.${index}.quantity`)">
                                            <div class="md:col-span-[13] w-full bg-red-50 border border-red-200 rounded-md p-3 mt-1 shadow-sm">
                                                <div class="flex items-start">
                                                    <svg class="h-4 w-4 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    <div>
                                                        <h4 class="text-xs font-bold text-red-800">Error en cantidad / Stock</h4>
                                                        <p class="text-[11px] text-red-700 font-medium mt-0.5" x-text="getError(`items.${index}.quantity`)"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            @if ($order->status !== $reservedStatus)
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
                                        name="client_name_display"
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
                                    value="{{ old('date', $order->date->format('Y-m-d\TH:i')) }}"
                                    max="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full rounded-md border-[{{ $errors->has('date') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                @error('date')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" id="status"
                                    x-model="status"
                                    class="w-full rounded-md border-[{{ $errors->has('status') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption['value'] }}"
                                            {{ old('status', $order->status) === $statusOption['value'] ? 'selected' : '' }}>
                                            {{ $statusOption['label'] }}
                                        </option>
                                    @endforeach
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
                                    class="w-full rounded-md border-[{{ $errors->has('payment_method') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                    :required="['{{ \App\Models\Order::STATUS_RESERVED }}', '{{ \App\Models\Order::STATUS_DELIVERED }}'].includes(status)">
                                    <option value="">Seleccionar...</option>
                                    <option value="cash"
                                        {{ (old('payment_method') ?? $order->payment_method) == 'cash' ? 'selected' : '' }}>
                                        Efectivo</option>
                                    <option value="transfer"
                                        {{ (old('payment_method') ?? $order->payment_method) == 'transfer' ? 'selected' : '' }}>
                                        Transferencia</option>
                                    <option value="mercadopago"
                                        {{ (old('payment_method') ?? $order->payment_method) == 'mercadopago' ? 'selected' : '' }}>
                                        Mercado Pago
                                    </option>
                                    <option value="other"
                                        {{ (old('payment_method') ?? $order->payment_method) == 'other' ? 'selected' : '' }}>
                                        Otro
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
                                    class="w-full rounded-md border-[{{ $errors->has('delivery_type') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                    <option value="showroom"
                                        {{ (old('delivery_type') ?? $order->delivery_type) == 'showroom' ? 'selected' : '' }}>
                                        Retiro en Showroom</option>
                                    <option value="shipping"
                                        {{ (old('delivery_type') ?? $order->delivery_type) == 'shipping' ? 'selected' : '' }}>
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
                                    class="w-full rounded-md border-[{{ $errors->has('shipping_cost') ? 'red-500' : 'gray-300' }}] shadow-sm focus:border-brand-pink focus:ring-brand-pink focus:ring-opacity-50 disabled:bg-gray-100 disabled:text-gray-500">

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

    </div>
@endsection
