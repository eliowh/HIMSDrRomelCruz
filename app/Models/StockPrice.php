<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    // Table name is not the Laravel convention (should be stock_prices), so set it explicitly
    protected $table = 'stock_price';

    // If your table doesn't have created_at/updated_at columns, disable timestamps
    public $timestamps = false;

    protected $fillable = [
        'item_code',
        'generic_name',
        'brand_name',
        'price',
        'quantity',
    ];
}
