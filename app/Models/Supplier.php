<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'company_name',
        'company_name_ar',
        'phone',
        'email',
        'gst_number',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

