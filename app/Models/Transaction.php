<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    public const FULFILLMENT_MODE_SEPARATE = 'separate';
    public const FULFILLMENT_MODE_COMBINED = 'combined';

    /*
    |--------------------------------------------------------------------------
    | Transaction Sources
    |--------------------------------------------------------------------------
    | "Website" (and NULL for legacy website rows) belongs to the online store.
    | "Booth" is the legacy POS source and remains on the offline transaction
    | page. "POS" marks transactions created by the current Kasir POS and is
    | the only source used by the Data Transaksi Kasir page.
    */
    public const SOURCE_WEBSITE = 'Website';
    public const SOURCE_BOOTH = 'Booth';
    public const SOURCE_POS = 'POS';
    public const SOURCE_STADIUM = 'Stadium';

    public const POS_SOURCES = [
        self::SOURCE_BOOTH,
        self::SOURCE_POS,
    ];

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

        'qris_reference_no',
        'qris_content',
        'qris_expired_at',
        'qris_response',

        'subtotal',
        'discount',
        'shipping',
        'actual_shipping_cost',
        'fulfillment_mode',
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
        'shipping_latitude',
        'shipping_longitude',
        'shipping_method',
        'fulfillment_date',
        'biteship_order_id',
        'biteship_tracking_id',
        'biteship_waybill_id',
        'biteship_status',
        'courier',
        'tracking_number',
        'courier_code',
        'courier_service_code',
        'pickup_date',
        'pickup_time_start',
        'pickup_time_end',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'va_expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_latitude' => 'decimal:7',
        'shipping_longitude' => 'decimal:7',
        'doku_response' => 'array',
        'qris_expired_at' => 'datetime',
        'qris_response' => 'array',
        'fulfillment_date' => 'date',
        'pickup_date' => 'date',
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
    public const SALES_REPORT_STATUSES = [
        'PAID',
        'COMPLETED',
        self::ORDER_PROCESSING,
        self::ORDER_SHIPPED,
        self::ORDER_COMPLETED,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * All POS-era sources (current POS and legacy Booth).
     *
     * Used by features that must keep recognising legacy Booth transactions,
     * such as QRIS payment-status polling.
     */
    public function scopeFromPosKasir($query)
    {
        return $query->whereIn('source', self::POS_SOURCES);
    }

    /**
     * Transactions created by the current Kasir POS only.
     *
     * The Data Transaksi Kasir page must never include legacy Booth rows.
     */
    public function scopeFromKasirPos($query)
    {
        return $query->where('source', self::SOURCE_POS);
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
    public function fulfillmentHold(): HasOne
    {
        return $this->hasOne(FulfillmentHold::class);
    }

    public function fulfillments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransactionFulfillment::class);
    }
    public function shipments(): HasMany
    {
        return $this->hasMany(TransactionShipment::class);
    }
}