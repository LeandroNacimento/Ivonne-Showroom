<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('admin.clients.index');
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Cliente creado con éxito.');
    }

    public function show(Client $client)
    {
        $client->load([
            'orders' => function ($query) {
                $query->latest('date')->with([
                    'items.product.images',
                    'items.variation.productColor'
                ]);
            }
        ]);

        // Append cover_url only here so it's available in the @json() blade output
        // without polluting global Product serialization (which would break Livewire hydration)
        foreach ($client->orders as $order) {
            $order->items->each(fn($item) => $item->product?->append('cover_url'));
        }

        $stats = [
            'total_orders' => $client->orders->count(),
            'total_spent' => $client->orders->sum('total'),
            'last_order' => $client->orders->first() ? $client->orders->first()->date->format('d/m/Y') : 'N/A',
        ];

        return view('admin.clients.show', compact('client', 'stats'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy(Client $client)
    {
        if ($client->orders()->exists()) {
            return back()->with('error', 'No se puede eliminar un cliente con pedidos registrados.');
        }

        $client->delete();
        return redirect()->route('admin.clients.index')->with('success', 'Cliente eliminado con éxito.');
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $query = trim($request->input('q'));

        if (!$query) {
            return response()->json([]);
        }

        $clients = Client::query()
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($clients);
    }
}
