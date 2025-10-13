<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'admission_id',
        'billing_number',
        'total_amount',
        'philhealth_deduction',
        'senior_pwd_discount',
        'net_amount',
        'status',
        'billing_date',
        'payment_date',
        'payment_amount',
        'change_amount',
        'processed_by',
        'room_charges',
        'professional_fees',
        'medicine_charges',
        'lab_charges',
        'other_charges',
        'is_philhealth_member',
        'is_senior_citizen',
        'is_pwd',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'billing_date' => 'datetime',
        'payment_date' => 'datetime',
        'is_philhealth_member' => 'boolean',
        'is_senior_citizen' => 'boolean',
        'is_pwd' => 'boolean',
        'total_amount' => 'decimal:2',
        'philhealth_deduction' => 'decimal:2',
        'senior_pwd_discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'room_charges' => 'decimal:2',
        'professional_fees' => 'decimal:2',
        'medicine_charges' => 'decimal:2',
        'lab_charges' => 'decimal:2',
        'other_charges' => 'decimal:2'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Calculate discounts
    public function calculateSeniorPwdDiscount()
    {
        if ($this->is_senior_citizen || $this->is_pwd) {
            return $this->total_amount * 0.20; // 20% discount
        }
        return 0;
    }

    // Calculate PhilHealth deduction based on ICD-10 codes
    public function calculatePhilhealthDeduction()
    {
        if (!$this->is_philhealth_member) {
            return 0;
        }

        // PhilHealth deduction should be based on Case Rate + Professional Fee combined.
        // This represents the total amount covered by PhilHealth for professional services.
        $deduction = 0;
        foreach ($this->billingItems as $item) {
            if ($item->item_type === 'professional') {
                $quantity = $item->quantity ?: 1;
                $caseRate = $item->case_rate ?: 0;
                $professionalFee = $item->unit_price ?: 0;
                
                // PhilHealth covers Case Rate + Professional Fee
                $deduction += (($caseRate + $professionalFee) * $quantity);
            }
        }

        return $deduction;
    }

    // Calculate final net amount
    public function calculateNetAmount()
    {
        $grossAmount = $this->total_amount ?? 0;
        $philhealthDeduction = $this->philhealth_deduction ?? $this->calculatePhilhealthDeduction();
        $seniorPwdDiscount = $this->senior_pwd_discount ?? $this->calculateSeniorPwdDiscount();
        
        $net = $grossAmount - $philhealthDeduction - $seniorPwdDiscount;

        // Ensure net amount is not negative. Client-side clamping is helpful for UX,
        // but enforce the non-negative rule server-side to prevent persisting negatives.
        return max(0, $net);
    }

    // Recalculate and sync all totals from billing items
    public function recalculateFromItems()
    {
        $this->room_charges = $this->billingItems()->where('item_type', 'room')->sum('total_amount');
        $this->professional_fees = $this->billingItems()->where('item_type', 'professional')->sum('total_amount');
        $this->medicine_charges = $this->billingItems()->where('item_type', 'medicine')->sum('total_amount');
        $this->lab_charges = $this->billingItems()->where('item_type', 'laboratory')->sum('total_amount');
        $this->other_charges = $this->billingItems()->where('item_type', 'other')->sum('total_amount');
        
        $this->total_amount = $this->room_charges + $this->professional_fees + 
                             $this->medicine_charges + $this->lab_charges + $this->other_charges;
        
        $this->philhealth_deduction = $this->calculatePhilhealthDeduction();
        $this->senior_pwd_discount = $this->calculateSeniorPwdDiscount();
        $this->net_amount = $this->calculateNetAmount();
        
        return $this;
    }
}
