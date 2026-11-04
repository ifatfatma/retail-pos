<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'gst_number',
        'total_spent',
        'total_orders',
        'status',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'status' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

