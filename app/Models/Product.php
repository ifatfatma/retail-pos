<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'unit_id',
        'description',
        'product_type',
        'track_inventory',
        'low_stock_threshold',
        'status',
    ];

    protected $casts = [
        'track_inventory' => 'boolean',
        'status' => 'boolean',
        'low_stock_threshold' => 'decimal:3',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}