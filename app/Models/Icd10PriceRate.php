<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Icd10PriceRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'icd_code',
        'description',
        'professional_fee',
        'room_rate_per_day',
        'medicine_allowance',
        'lab_fee',
        'philhealth_coverage_percentage',
        'is_active'
    ];

    protected $casts = [
        'professional_fee' => 'decimal:2',
        'room_rate_per_day' => 'decimal:2',
        'medicine_allowance' => 'decimal:2',
        'lab_fee' => 'decimal:2',
        'philhealth_coverage_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'icd_code', 'icd_code');
    }

    // Calculate total estimated charges for this ICD-10 code
    public function getTotalEstimatedCharges($days = 1)
    {
        return [
            'professional_fee' => $this->professional_fee,
            'room_charges' => $this->room_rate_per_day * $days,
            'medicine_allowance' => $this->medicine_allowance,
            'lab_fee' => $this->lab_fee,
            'total' => $this->professional_fee + ($this->room_rate_per_day * $days) + $this->medicine_allowance + $this->lab_fee
        ];
    }

    // Calculate PhilHealth coverage amount
    public function calculatePhilhealthCoverage($totalAmount)
    {
        return ($this->philhealth_coverage_percentage / 100) * $totalAmount;
    }

    // Get active ICD-10 codes
    public static function getActiveRates()
    {
        return self::where('is_active', true)->orderBy('icd_code')->get();
    }

    // Search ICD-10 codes
    public static function search($query)
    {
        return self::where('is_active', true)
                  ->where(function($q) use ($query) {
                      $q->where('icd_code', 'LIKE', '%' . $query . '%')
                        ->orWhere('description', 'LIKE', '%' . $query . '%');
                  })
                  ->orderBy('icd_code')
                  ->get();
    }
}
