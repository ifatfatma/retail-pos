<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = [
        'customer_id',
        'guest_token',
        'product_id',
        'product_variant_id',
        'initial_price',
        'notify_price_drop',
        'notify_back_in_stock',
    ];

    protected $casts = [
        'initial_price' => 'decimal:2',
        'notify_price_drop' => 'boolean',
        'notify_back_in_stock' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

