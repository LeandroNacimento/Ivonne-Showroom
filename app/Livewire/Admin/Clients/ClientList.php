<?php

namespace App\Livewire\Admin\Clients;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ClientList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = User::where('role', 'client')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.clients.client-list', [
            'clients' => $clients
        ]);
    }
}
