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

    // Query scopes for reporting
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        $days = (int) $days; // Ensure integer type
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    // Helper methods for reports
    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isExpiringSoon($days = 30)
    {
        $days = (int) $days; // Ensure integer type
        if (!$this->expiry_date) return false;
        return $this->expiry_date->isAfter(now()) && 
               $this->expiry_date->isBefore(now()->addDays($days));
    }

    public function isExpired()
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->isPast();
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) return null;
        return now()->diffInDays($this->expiry_date, false);
    }
}
