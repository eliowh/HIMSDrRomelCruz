<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    // Table name set to match the created migration table name
    protected $table = 'stock_price';

    // Enable timestamps as the migration created these columns
    public $timestamps = true;

    // Define fillable columns based on the migration
    protected $fillable = [
        'item_code',
        'generic_name',
        'brand_name',
        'price',
        'quantity',
    ];
    
    // Default values for nullable fields
    protected $attributes = [
        'price' => 0,
        'quantity' => 0,
    ];
    
    /**
     * Ensure quantity is always at least 0 (never null)
     */
    public function getQuantityAttribute($value)
    {
        return $value ?? 0;
    }
}
