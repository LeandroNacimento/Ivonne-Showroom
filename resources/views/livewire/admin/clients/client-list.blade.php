<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Clientes</h1>
        <a href="{{ route('admin.clients.create') }}" class="bg-brand-pink text-white px-4 py-2 rounded-md hover:bg-brand-heart transition-colors">
            Nuevo Cliente
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden" wire:loading.class="opacity-50 pointer-events-none">
        <!-- Search Bar -->
        <div class="p-4 border-b border-gray-200">
            <div class="relative max-w-md w-full">
                <input 
                    wire:model.live="search" 
                    type="text" 
                    class="block w-full rounded-md border-0 py-1.5 pl-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6" 
                    placeholder="Buscar por nombre, email o teléfono..."
                >
                <div wire:loading.class.remove="hidden" wire:target="search" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                    <span class="text-sm text-gray-500 font-medium">Buscando...</span>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedidos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Gastado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                <tr wire:key="client-{{ $client->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $client->name }}</div>
                        @if($client->instagram)
                            <div class="text-sm text-gray-500">{{ $client->instagram }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $client->phone ?? '-' }}</div>
                        <div class="text-sm text-gray-500">{{ $client->email ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->orders_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($client->orders_sum_total ?? 0, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.clients.show', $client) }}" class="text-blue-600 hover:text-blue-900 mr-4">Ver</a>
                        <a href="{{ route('admin.clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                 <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron clientes.
                    </td>
                </tr>
                @endforelse
            </tbody>
                </table>
            </div><!-- /overflow-x-auto -->
            <div class="pointer-events-none absolute right-0 top-0 h-full w-8 bg-gradient-to-l from-white to-transparent"></div>
        </div><!-- /relative -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $clients->links() }}
        </div>
    </div>
</div>
