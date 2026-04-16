<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    protected OrderService $orderService;

    public $search = '';

    public $status = '';

    public $date_from = '';

    public $date_to = '';

    public ?string $feedbackMessage = null;

    public ?string $feedbackType = null;

    public function boot(OrderService $orderService): void
    {
        $this->orderService = $orderService;
    }

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

    public function changeStatus(int $orderId, string $newStatus, ?string $paymentMethod = null): void
    {
        $this->feedbackMessage = null;
        $this->feedbackType = null;

        $order = Order::query()->findOrFail($orderId);
        $oldStatus = $order->status;

        if ($order->isTerminal()) {
            $this->handleStatusUpdateFailure($orderId, $oldStatus, "El pedido está en estado '{$oldStatus}' y no puede ser modificado.");

            return;
        }

        if ($oldStatus === $newStatus) {
            return;
        }

        if (! in_array($newStatus, $this->orderService->availableStatusesFor($order), true)) {
            $this->handleStatusUpdateFailure($orderId, $oldStatus, "La transición de '{$oldStatus}' a '{$newStatus}' no está permitida.");

            return;
        }

        try {
            $updatedOrder = $this->orderService->transitionStatus($order, $newStatus, [
                'payment_method' => $paymentMethod,
            ]);
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Estado del pedido #{$updatedOrder->id} actualizado a ".Order::statusLabel($updatedOrder->status).'.';

            $this->dispatch('order-status-updated', orderId: $updatedOrder->id, status: $updatedOrder->status);
        } catch (ValidationException $exception) {
            $message = $exception->validator->errors()->first() ?: 'No se pudo actualizar el estado del pedido.';
            $this->handleStatusUpdateFailure($orderId, $oldStatus, $message);
        } catch (\Throwable $exception) {
            report($exception);
            $this->handleStatusUpdateFailure($orderId, $oldStatus, 'Ocurrió un error inesperado al actualizar el estado del pedido.');
        }
    }

    public function statusOptionsFor(Order $order): array
    {
        return $this->orderService->statusOptionsFor($order);
    }

    private function handleStatusUpdateFailure(int $orderId, string $status, string $message): void
    {
        $this->feedbackType = 'error';
        $this->feedbackMessage = $message;

        $this->dispatch('order-status-update-failed', orderId: $orderId, status: $status);
    }

    public function render()
    {
        $orders = Order::query()
            ->with('client')
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%'.$this->search.'%')
                        ->orWhereHas('client', function ($subQ) {
                            $subQ->where('name', 'like', '%'.$this->search.'%');
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
            'orders' => $orders,
        ]);
    }
}
