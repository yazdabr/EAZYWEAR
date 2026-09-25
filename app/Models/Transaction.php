<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrderStatusHistory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'transaction_date',
        'payment_method',

        'doku_request_id',
        'doku_payment_id',
        'va_number',
        'va_bank',
        'va_expired_at',

        'subtotal',
        'discount',
        'shipping',
        'total',
        'status',
        'paid_at',
        'doku_response',
        'source',

        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_district',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_method',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'va_expired_at' => 'datetime',
        'paid_at' => 'datetime',

        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',

        'doku_response' => 'array',
    ];

    public const ORDER_CREATED = 'ORDER_CREATED';
    public const PAYMENT_CONFIRMED = 'PAYMENT_CONFIRMED';
    public const ORDER_PROCESSING = 'ORDER_PROCESSING';
    public const ORDER_SHIPPED = 'ORDER_SHIPPED';
    public const ORDER_COMPLETED = 'ORDER_COMPLETED';
    public const ORDER_CANCELLED = 'ORDER_CANCELLED';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function orderStatusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->latest();
    }

    public function latestOrderStatus()
    {
        return $this->orderStatusHistories()
            ->first();
    }

    public function addStatusHistory(string $status, ?string $note = null): OrderStatusHistory
    {
        return $this->orderStatusHistories()->create([
            'status' => $status,
            'note' => $note,
        ]);
    }
}