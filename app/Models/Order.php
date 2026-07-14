<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'paid_at'    => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    protected $appends = ['order_number'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isEditable(): bool
    {
        return ! in_array($this->status, ['paid', 'delivered']);
    }

    public function getOrderNumberAttribute()
    {
        return str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
