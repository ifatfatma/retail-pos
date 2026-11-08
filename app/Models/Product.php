<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'category_id',
        'brand_id',
        'unit_id',
        'description',
        'description_ar',
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

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function getImageUrlAttribute()
    {
        $primary = $this->relationLoaded('images')
            ? ($this->images->firstWhere('is_primary', true) ?: $this->images->first())
            : ($this->relationLoaded('primaryImage') && $this->primaryImage
                ? $this->primaryImage
                : ($this->images()->where('is_primary', true)->first() ?: $this->images()->first()));

        if ($primary && $primary->image_path) {
            if (filter_var($primary->image_path, FILTER_VALIDATE_URL)) {
                return $primary->image_path;
            }
            return asset('storage/' . ltrim($primary->image_path, '/'));
        }

        return null;
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}