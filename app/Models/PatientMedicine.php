<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'pharmacy_request_id',
        'patient_no',
        'patient_name',
        'item_code',
        'generic_name',
        'brand_name',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'dispensed_by',
        'dispensed_at',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the patient that owns the medicine record
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the pharmacy request that this medicine record is based on
     */
    public function pharmacyRequest()
    {
        return $this->belongsTo(PharmacyRequest::class);
    }

    /**
     * Get the user who dispensed this medicine
     */
    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    /**
     * Get the admission through the pharmacy request
     */
    public function admission()
    {
        return $this->hasOneThrough(Admission::class, PharmacyRequest::class, 'id', 'id', 'pharmacy_request_id', 'admission_id');
    }

    /**
     * Get the admission ID through the pharmacy request
     */
    public function getAdmissionIdAttribute()
    {
        return $this->pharmacyRequest?->admission_id;
    }

    /**
     * Calculate the total price based on unit price and quantity
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->unit_price * $this->quantity;
        return $this;
    }

    /**
     * Scope to get medicines for a specific patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to get medicines dispensed on a specific date
     */
    public function scopeDispensedOn($query, $date)
    {
        return $query->whereDate('dispensed_at', $date);
    }

    /**
     * Scope to get medicines dispensed by a specific user
     */
    public function scopeDispensedBy($query, $userId)
    {
        return $query->where('dispensed_by', $userId);
    }
}
