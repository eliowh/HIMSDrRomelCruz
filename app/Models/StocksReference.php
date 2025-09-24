<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocksReference extends Model
{
    use HasFactory;

    protected $table = 'stocksreference';

    protected $fillable = [
        'COL 1', // Item Code
        'COL 2', // Generic Name
        'COL 3', // Brand Name
        'COL 4', // Price
        'COL 5', // Additional Info
    ];

    // Custom query scope to exclude header row
    public function scopeExcludeHeader($query)
    {
        return $query->where('COL 1', '!=', 'Item Code');
    }

    // Scope to filter items with non-empty generic names
    public function scopeHasGenericName($query)
    {
        return $query->where('COL 2', '!=', '')
                    ->whereNotNull('COL 2');
    }

    // Scope to filter items with non-empty brand names
    public function scopeHasBrandName($query)
    {
        return $query->where('COL 3', '!=', '')
                    ->whereNotNull('COL 3');
    }

    // Accessor methods for better readability
    public function getItemCodeAttribute()
    {
        return $this->attributes['COL 1'];
    }

    public function getGenericNameAttribute()
    {
        return $this->attributes['COL 2'];
    }

    public function getBrandNameAttribute()
    {
        return $this->attributes['COL 3'];
    }

    public function getPriceAttribute()
    {
        return $this->attributes['COL 4'];
    }

    public function getAdditionalInfoAttribute()
    {
        return $this->attributes['COL 5'];
    }

    // Scope methods for searching
    public function scopeByItemCode($query, $itemCode)
    {
        return $query->where('COL 1', $itemCode);
    }

    public function scopeByGenericName($query, $genericName)
    {
        return $query->where('COL 2', 'like', '%' . $genericName . '%');
    }

    public function scopeByBrandName($query, $brandName)
    {
        return $query->where('COL 3', 'like', '%' . $brandName . '%');
    }
}