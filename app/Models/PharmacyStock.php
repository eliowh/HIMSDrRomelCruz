<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyStock extends Model
{
    // Table name
    protected $table = 'pharmacystocks';

    // Enable timestamps
    public $timestamps = true;

    // Define fillable columns (same as stock_price)
    protected $fillable = [
        'item_code',
        'generic_name',
        'brand_name',
        'price',
        'quantity',
        'expiry_date',
        'reorder_level',
        'supplier',
        'batch_number',
        'date_received',
    ];
    
    // Default values for nullable fields
    protected $attributes = [
        'price' => 0,
        'quantity' => 0,
        'reorder_level' => 10,
    ];

    // Cast attributes
    protected $casts = [
        'expiry_date' => 'date',
        'date_received' => 'date',
        'price' => 'decimal:2',
    ];
    
    /**
     * Ensure quantity is always at least 0 (never null)
     */
    public function getQuantityAttribute($value)
    {
        return $value ?? 0;
    }
}
