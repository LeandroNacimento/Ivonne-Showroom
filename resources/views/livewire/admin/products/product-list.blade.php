<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Productos</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-brand-pink text-white px-4 py-2 rounded-md hover:bg-brand-heart transition-colors">
            Nuevo Producto
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Search Bar -->
        <div class="p-4 border-b border-gray-200">
            <div class="relative max-w-md w-full">
                <input 
                    wire:model.live="search" 
                    type="text" 
                    class="block w-full rounded-md border-0 py-1.5 pl-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6" 
                    placeholder="Buscar productos..."
                >
                <div wire:loading.class.remove="hidden" wire:target="search" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                    <span class="text-sm text-gray-500 font-medium">Buscando...</span>
                </div>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                <tr wire:key="product-{{ $product->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                         @if($product->images->first())
                            <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-md object-cover">
                        @else
                            <span class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center text-gray-500 text-xs">N/A</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($product->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->variations->sum('stock') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                         <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                         <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron productos para esta búsqueda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links('pagination::simple-tailwind') }}
        </div>
    </div>
</div>
