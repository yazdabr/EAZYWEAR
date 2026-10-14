<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransactionShipment extends Model
{
    use HasFactory;

    public const TYPE_SEPARATE = 'separate';
    public const TYPE_COMBINED = 'combined';

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_SHIPPED = 'SHIPPED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'transaction_id',
        'shipment_type',
        'shipping_method',
        'shipping_cost',
        'pickup_date',
        'pickup_time_start',
        'pickup_time_end',
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
        'courier_code',
        'courier_service_code',
        'biteship_order_id',
        'biteship_tracking_id',
        'biteship_waybill_id',
        'biteship_status',
        'tracking_number',
        'courier',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'pickup_date' => 'date',
            'shipping_latitude' => 'decimal:7',
            'shipping_longitude' => 'decimal:7',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function fulfillments(): BelongsToMany
    {
        return $this->belongsToMany(
            TransactionFulfillment::class,
            'transaction_shipment_fulfillment',
            'transaction_shipment_id',
            'transaction_fulfillment_id'
        )->withTimestamps();
    }
}
