<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class OrderList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $date_from = '';
    public $date_to = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingDateFrom()
    {
        $this->resetPage();
    }
    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function render()
    {
        $orders = Order::query()
            ->with('client')
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($subQ) {
                            $subQ->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->date_from, function ($query) {
                $query->whereDate('date', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query) {
                $query->whereDate('date', '<=', $this->date_to);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.orders.order-list', [
            'orders' => $orders
        ]);
    }
}
