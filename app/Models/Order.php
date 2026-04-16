<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pendiente';

    public const STATUS_RESERVED = 'reservado';

    public const STATUS_DELIVERED = 'entregado';

    public const STATUS_CANCELLED = 'cancelado';

    public const TERMINAL_STATES = [
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_RESERVED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'client_id',
        'status',
        'payment_method',
        'delivery_type',
        'shipping_cost',
        'total',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATES, true);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_RESERVED => 'Reservado',
            self::STATUS_DELIVERED => 'Entregado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => ucfirst($status),
        };
    }
}
