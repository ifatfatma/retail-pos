<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'barcode',
        'purchase_price',
        'selling_price',
        'mrp',
        'tax_rate',
        'is_default',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'product_variant_id');
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'product_variant_id');
    }
}