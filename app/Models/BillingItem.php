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
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'icd_code',
        'date_charged'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'date_charged' => 'datetime'
    ];

    const ITEM_TYPES = [
        'room' => 'Room Charges',
        'medicine' => 'Medicine',
        'laboratory' => 'Laboratory',
        'professional' => 'Professional Fee',
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
}
