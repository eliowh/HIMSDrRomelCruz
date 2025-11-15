<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'admission_number',
        'room_no',
        'admission_type',
        'service',
        'doctor_name',
        'doctor_type',
        'admission_diagnosis',
        'final_diagnosis',
        'final_diagnosis_description',
        'admission_date',
        'discharge_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($admission) {
            if (!$admission->admission_number) {
                $admission->admission_number = self::generateAdmissionNumber();
            }
            if (!$admission->admission_date) {
                $admission->admission_date = now();
            }
        });
    }

    /**
     * Generate unique admission number
     */
    public static function generateAdmissionNumber()
    {
        $year = date('Y');
        $lastAdmission = self::where('admission_number', 'like', "ADM-{$year}-%")
                            ->orderBy('id', 'desc')
                            ->first();
        
        if ($lastAdmission) {
            $lastNumber = (int) substr($lastAdmission->admission_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'ADM-' . $year . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the patient associated with the admission
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get billings associated with this admission
     */
    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    /**
     * Get lab orders for this admission
     */
    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    /**
     * Scope for active admissions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for discharged admissions
     */
    public function scopeDischarged($query)
    {
        return $query->where('status', 'discharged');
    }

    /**
     * Check if admission is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Mark admission as discharged
     */
    public function discharge($dischargeDate = null)
    {
        $this->update([
            'status' => 'discharged',
            'discharge_date' => $dischargeDate ?? now(),
        ]);
    }
}
