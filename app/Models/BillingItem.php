<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'item_type',
        'item_code',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'case_rate',
        'date_charged',
        'icd_code'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'case_rate' => 'decimal:2',
        'date_charged' => 'datetime',
        'service_date' => 'date'
    ];

    const ITEM_TYPES = [
        'room' => 'Room Charges',
        'medicine' => 'Medicine',
        'laboratory' => 'Laboratory',
        'professional' => 'ICD Fee',
        'other' => 'Other Charges'
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function icd10NamePriceRate()
    {
        return Icd10NamePriceRate::getByCode($this->icd_code);
    }

    // Automatically calculate total amount
    public function calculateTotalAmount()
    {
        return $this->quantity * $this->unit_price;
    }

    // Get formatted item type
    public function getFormattedItemType()
    {
        return self::ITEM_TYPES[$this->item_type] ?? $this->item_type;
    }

    // Model events to auto-calculate total_amount
    protected static function booted()
    {
        static::creating(function ($item) {
            if (!$item->total_amount) {
                $item->total_amount = $item->calculateTotalAmount();
            }
        });

        static::updating(function ($item) {
            if ($item->isDirty(['quantity', 'unit_price']) && !$item->isDirty('total_amount')) {
                $item->total_amount = $item->calculateTotalAmount();
            }
        });
    }
}
