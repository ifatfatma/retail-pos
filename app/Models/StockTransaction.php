<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'quantity',
        'balance_after',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'balance_after' => 'decimal:3',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

