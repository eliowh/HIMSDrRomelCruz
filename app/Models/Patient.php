<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Patient extends Model
{
    protected $fillable = [
        'patient_no',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'age_years',
        'age_months',
        'age_days',
        'province',
        'city',
        'barangay',
        'nationality',
        'room_no',
        'admission_type',
        'service',
        'doctor_name',
        'doctor_type',
        'admission_diagnosis',
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
     * Get the patient's age.
     */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->diffInYears(now());
    }

    /**
     * The doctors that belong to the patient.
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'patient_id', 'doctor_id')
                    ->where('role', 'doctor');
    }

    /**
     * Scope a query to only include active patients.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'discharged');
    }

    /**
     * Scope a query to only include patients by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

