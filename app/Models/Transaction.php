<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /*
    |--------------------------------------------------------------------------
    | Order Status
    |--------------------------------------------------------------------------
    */

    public const ORDER_CREATED = 'ORDER_CREATED';
    public const PAYMENT_CONFIRMED = 'PAYMENT_CONFIRMED';
    public const ORDER_PROCESSING = 'ORDER_PROCESSING';
    public const ORDER_SHIPPED = 'ORDER_SHIPPED';
    public const ORDER_COMPLETED = 'ORDER_COMPLETED';
    public const ORDER_CANCELLED = 'ORDER_CANCELLED';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    public function notifications(): HasMany
    {
        return $this->hasMany(TransactionNotification::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Management
    |--------------------------------------------------------------------------
    */

    public function addStatusHistory(
        string $status,
        ?string $note = null
    ): void {
        $this->orderStatusHistories()->create([
            'status' => $status,
            'note' => $note,
        ]);
    }

    public function updateStatus(
        string $status,
        ?string $note = null
    ): void {
        $this->update([
            'status' => $status,
        ]);

        $this->addStatusHistory(
            $status,
            $note
        );
    }

    public function latestOrderStatus(): ?OrderStatusHistory
    {
        return $this->orderStatusHistories()
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::ORDER_CREATED => 'Pesanan Dibuat',
            self::PAYMENT_CONFIRMED => 'Pembayaran Dikonfirmasi',
            self::ORDER_PROCESSING => 'Sedang Diproses',
            self::ORDER_SHIPPED => 'Pesanan Dikirim',
            self::ORDER_COMPLETED => 'Pesanan Selesai',
            self::ORDER_CANCELLED => 'Pesanan Dibatalkan',
            'PENDING' => 'Menunggu Pembayaran',
            'PAID' => 'Pembayaran Berhasil',
            'EXPIRED' => 'Pembayaran Kadaluarsa',
            default => $this->status,
        };
    }
}