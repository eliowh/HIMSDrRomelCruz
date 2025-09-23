<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    // Table name is not the Laravel convention (should be stocks_prices), so set it explicitly
    protected $table = 'stocks_prices';

    // If your table doesn't have created_at/updated_at columns, disable timestamps
    public $timestamps = false;

    // These are the actual column names in the database
    protected $fillable = [
        'COL 1',
        'COL 2',
        'COL 3',
        'COL 4',
        'COL 5',
    ];
    
    /**
     * Mapping of column names to semantic names
     */
    const COLUMN_MAPPING = [
        'item_code' => 'COL 1',
        'generic_name' => 'COL 2',
        'brand_name' => 'COL 3',
        'price' => 'COL 4',
        'quantity' => 'COL 5'
    ];
    
    /**
     * Get the column name for a semantic field
     */
    public static function getColumnName($field)
    {
        return self::COLUMN_MAPPING[$field] ?? $field;
    }
    
    /**
     * Accessor for item_code attribute
     */
    public function getItemCodeAttribute()
    {
        return $this->attributes[self::COLUMN_MAPPING['item_code']] ?? null;
    }
    
    /**
     * Accessor for generic_name attribute
     */
    public function getGenericNameAttribute()
    {
        return $this->attributes[self::COLUMN_MAPPING['generic_name']] ?? null;
    }
    
    /**
     * Accessor for brand_name attribute
     */
    public function getBrandNameAttribute()
    {
        return $this->attributes[self::COLUMN_MAPPING['brand_name']] ?? null;
    }
    
    /**
     * Accessor for price attribute
     */
    public function getPriceAttribute()
    {
        return $this->attributes[self::COLUMN_MAPPING['price']] ?? null;
    }
    
    /**
     * Accessor for quantity attribute
     */
    public function getQuantityAttribute()
    {
        return $this->attributes[self::COLUMN_MAPPING['quantity']] ?? 0;
    }
    
    /**
     * Scope to order by a semantic field name
     */
    public function scopeOrderBySemantic($query, $field, $direction = 'asc')
    {
        return $query->orderBy(self::getColumnName($field), $direction);
    }
    
    /**
     * Scope to filter by a semantic field name
     */
    public function scopeWhereSemantic($query, $field, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        return $query->where(self::getColumnName($field), $operator, $value);
    }
    
    /**
     * Scope to filter by semantic field like a value
     */
    public function scopeWhereSemanticLike($query, $field, $value)
    {
        return $query->where(self::getColumnName($field), 'like', "%{$value}%");
    }
}
