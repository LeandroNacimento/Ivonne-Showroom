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
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        Client::create($request->all());

        return redirect()->route('admin.clients.index')->with('success', 'Cliente creado con éxito.');
    }

    public function show(Client $client)
    {
        $client->load([
            'orders' => function ($query) {
                $query->latest('date');
            }
        ]);

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
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $client->update($request->all());

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
}
