<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10NamePriceRate extends Model
{
    protected $table = 'icd10namepricerate';
    
    // No timestamps in the existing table
    public $timestamps = false;
    
    // Map the generic column names to meaningful attributes
    protected $fillable = [
        'COL 1', // ICD Code
        'COL 2', // Description
        'COL 3', // Price/Rate 1  
        'COL 4'  // Price/Rate 2
    ];

    // Accessor for ICD code
    public function getIcdCodeAttribute()
    {
        // Handle both aliased and raw column access
        return $this->attributes['icd_code'] ?? $this->attributes['COL 1'] ?? null;
    }

    // Accessor for description
    public function getDescriptionAttribute()
    {
        // Handle both aliased and raw column access
        return $this->attributes['description'] ?? $this->attributes['COL 2'] ?? null;
    }

    // Accessor for professional fee (assuming COL 3 is professional fee)
    public function getProfessionalFeeAttribute()
    {
        $value = $this->attributes['professional_fee'] ?? $this->attributes['COL 3'] ?? 0;
        
        // Clean the value - remove commas and convert to float
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }
        
        return is_numeric($value) ? (float)$value : 0.00;
    }

    // Accessor for additional rate (assuming COL 4 is additional rate info)
    public function getAdditionalRateAttribute()
    {
        $value = $this->attributes['additional_rate'] ?? $this->attributes['COL 4'] ?? 0;
        
        // Clean the value - remove commas and convert to float
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }
        
        return is_numeric($value) ? (float)$value : 0.00;
    }

    // Search ICD codes and descriptions
    public static function search($query)
    {
        return self::where('COL 1', 'LIKE', '%' . $query . '%')
                  ->orWhere('COL 2', 'LIKE', '%' . $query . '%')
                  ->limit(20)
                  ->get()
                  ->map(function ($item) {
                      return [
                          'icd_code' => $item->icd_code,
                          'description' => $item->description,
                          'professional_fee' => $item->professional_fee,
                          'additional_rate' => $item->additional_rate
                      ];
                  });
    }

    // Get ICD record by code
    public static function getByCode($code)
    {
        return self::where('COL 1', $code)->first();
    }

    // Get all active ICD codes for dropdown
    public static function getAllCodes()
    {
        return self::selectRaw('`COL 1` as icd_code, `COL 2` as description, `COL 3` as professional_fee')
                  ->whereNotNull('COL 1')
                  ->where('COL 1', '!=', '')
                  ->orderBy('COL 1')
                  ->get()
                  ->map(function($item) {
                      // Clean and convert professional fee
                      $fee = $item->getAttributes()['professional_fee'] ?? 0;
                      if (is_string($fee)) {
                          $fee = str_replace(',', '', $fee);
                      }
                      
                      return (object)[
                          'icd_code' => $item->getAttributes()['icd_code'],
                          'description' => $item->getAttributes()['description'],
                          'professional_fee' => is_numeric($fee) ? (float)$fee : 0.00
                      ];
                  });
    }

    // Calculate estimated charges for this ICD code
    public function getEstimatedCharges($days = 1)
    {
        $professionalFee = $this->professional_fee;
        $additionalRate = $this->additional_rate;
        
        return [
            'professional_fee' => $professionalFee,
            'room_rate_per_day' => $additionalRate, // Assuming COL 4 might be room rate
            'medicine_allowance' => $professionalFee * 0.5, // 50% of professional fee as estimate
            'lab_fee' => $professionalFee * 0.3, // 30% of professional fee as estimate
            'estimated_total' => $professionalFee + ($additionalRate * $days) + ($professionalFee * 0.5) + ($professionalFee * 0.3)
        ];
    }

    // Calculate PhilHealth coverage (estimated at 60% for most cases)
    public function getPhilhealthCoveragePercentage()
    {
        // Basic PhilHealth coverage estimation
        // This could be made more sophisticated based on ICD code categories
        $code = $this->icd_code;
        
        if (str_starts_with($code, 'O')) {
            return 80; // Higher coverage for pregnancy/childbirth
        } elseif (str_starts_with($code, 'I')) {
            return 70; // Good coverage for cardiovascular
        } elseif (str_starts_with($code, 'A') || str_starts_with($code, 'B')) {
            return 75; // Good coverage for infectious diseases
        } else {
            return 60; // Standard coverage
        }
    }
}