<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'uuid', 'company_id', 'branch_id', 'user_id', 'customer_id', 'customer_name', 'customer_ip', 'table_id',
        'invoice_no', 'midtrans_order_id', 'midtrans_qr_url', 'midtrans_status',
        'type', 'source', 'preferred_payment', 'kitchen_status', 'status',
        'subtotal', 'discount_amount', 'tax_amount', 'total',
        'paid_amount', 'change_amount', 'points_earned', 'points_used',
        'notes', 'sync_uuid', 'synced_at', 'created_offline_at',
        'xendit_qr_id', 'payment_proof', 'rejection_reason',
    ];

    protected $casts = [
        'subtotal'           => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'total'              => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'change_amount'      => 'decimal:2',
        'synced_at'          => 'datetime',
        'created_offline_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (! $order->uuid) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function branch()    { return $this->belongsTo(Branch::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function customer()  { return $this->belongsTo(Customer::class); }
    public function table()     { return $this->belongsTo(Table::class); }
    public function items()     { return $this->hasMany(OrderItem::class); }
    public function payments()  { return $this->hasMany(Payment::class); }
}
