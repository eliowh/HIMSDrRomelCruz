<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_no',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'province',
        'city',
        'barangay',
        'nationality',
        // admission fields
        'room_no',
        'admission_type',
        'service',
        'doctor_name',
        'doctor_type',
        'admission_diagnosis',
    ];

    protected $appends = [
        'age',
        'age_years',
        'age_months',
        'age_days',
    ];

    protected $hidden = [
        'medicines',
        'pharmacyRequests',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($patient) {
            if (empty($patient->patient_no)) {
                $max = (int) DB::table('patients')->max('patient_no');
                $patient->patient_no = ($max >= 250001) ? ($max + 1) : 250001;
            }
        });
    }

    /**
     * Get the patient's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the patient's formatted patient number.
     */
    public function getPatientIdAttribute(): string
    {
        return 'P' . str_pad($this->patient_no, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the patient's age.
     */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->diffInYears(now()) : 0;
    }

    /**
     * Computed granular age parts from date_of_birth
     */
    public function getAgeYearsAttribute(): ?int
    {
        if (!$this->date_of_birth) return null;
        $diff = $this->date_of_birth->diff(now());
        return $diff->y;
    }

    public function getAgeMonthsAttribute(): ?int
    {
        if (!$this->date_of_birth) return null;
        $diff = $this->date_of_birth->diff(now());
        return $diff->m;
    }

    public function getAgeDaysAttribute(): ?int
    {
        if (!$this->date_of_birth) return null;
        $diff = $this->date_of_birth->diff(now());
        return $diff->d;
    }

    /**
     * Get the medicines dispensed to this patient
     */
    public function medicines()
    {
        return $this->hasMany(PatientMedicine::class);
    }

    /**
     * Get the pharmacy requests for this patient
     */
    public function pharmacyRequests()
    {
        return $this->hasMany(PharmacyRequest::class);
    }

    /**
     * Get the lab orders for this patient
     */
    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    /**
     * Get all billable services for this patient
     */
    public function getBillableServicesAttribute()
    {
        $services = collect();
        
        // Add admission diagnosis as a service
        if ($this->admission_diagnosis) {
            $icd = \App\Models\Icd10NamePriceRate::where('COL 1', $this->admission_diagnosis)->first();
            if ($icd) {
                $services->push([
                    'type' => 'diagnosis',
                    'description' => $icd->description ?? 'Admission Diagnosis',
                    'icd_code' => $this->admission_diagnosis,
                    'professional_fee' => $icd->professional_fee ?? 0,
                    'quantity' => 1,
                    'source' => 'admission'
                ]);
            }
        }
        
        // Add completed lab orders
        foreach ($this->labOrders()->where('status', 'completed')->get() as $lab) {
            $services->push([
                'type' => 'laboratory',
                'description' => $lab->test_requested,
                'icd_code' => null,
                'professional_fee' => $lab->price ?? 0,
                'quantity' => 1,
                'source' => 'lab_order'
            ]);
        }
        
        // Add pharmacy requests (dispensed medicines)
        foreach ($this->pharmacyRequests()->where('status', 'dispensed')->get() as $pharmacy) {
            $services->push([
                'type' => 'medicine',
                'description' => $pharmacy->medicine_name ?? 'Medicine',
                'icd_code' => null,
                'professional_fee' => $pharmacy->price ?? 0,
                'quantity' => $pharmacy->quantity ?? 1,
                'source' => 'pharmacy'
            ]);
        }
        
        return $services;
    }

    /**
     * Get display name for billing
     */
    public function getDisplayNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }
}

