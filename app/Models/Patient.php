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
        'sex',
        'contact_number',
        'date_of_birth',
        'province',
        'city',
        'barangay',
        'nationality',
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
     * Get the admissions for this patient
     */
    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    /**
     * Get the current active admission
     */
    public function currentAdmission()
    {
        return $this->hasOne(Admission::class)->where('status', 'active')->latest();
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
        
        // Add room charges if patient has a room assigned
        if ($this->room_no) {
            $room = \App\Models\Room::where('COL 1', $this->room_no)->first();
            if ($room) {
                // Safely get room price and clean it
                $rawPrice = $room->getAttributes()['COL 2'] ?? '0';
                $cleanPrice = is_string($rawPrice) ? str_replace(',', '', $rawPrice) : $rawPrice;
                $roomPrice = is_numeric($cleanPrice) ? (float)$cleanPrice : 0.00;
                
                $services->push([
                    'type' => 'room',
                    'description' => 'Room: ' . $this->room_no,
                    'icd_code' => null,
                    'unit_price' => $roomPrice,
                    'quantity' => 1,
                    'source' => 'room'
                ]);
            }
        }
        
        // Add admission diagnosis as professional fee (ICD Code only)
        if ($this->admission_diagnosis) {
            $icd = \App\Models\Icd10NamePriceRate::where('COL 1', $this->admission_diagnosis)->first();
            if ($icd) {
                // COL 3 is Case Rate (higher amount), COL 4 is Professional Fee (lower amount)
                $rawCaseRate = $icd->getAttributes()['COL 3'] ?? '0';
                $cleanCaseRate = is_string($rawCaseRate) ? str_replace(',', '', $rawCaseRate) : $rawCaseRate;
                $caseRate = is_numeric($cleanCaseRate) ? (float)$cleanCaseRate : 0.00;
                
                $rawProfFee = $icd->getAttributes()['COL 4'] ?? '0';
                $cleanProfFee = is_string($rawProfFee) ? str_replace(',', '', $rawProfFee) : $rawProfFee;
                $professionalFee = is_numeric($cleanProfFee) ? (float)$cleanProfFee : 0.00;
                
                $services->push([
                    'type' => 'professional',
                    'description' => $this->admission_diagnosis . ' - ' . ($icd->description ?? 'Diagnosis'),
                    'icd_code' => $this->admission_diagnosis,
                    'unit_price' => $professionalFee, // This will be the editable professional fee
                    'case_rate' => $caseRate, // This will be read-only
                    'quantity' => 1,
                    'source' => 'admission'
                ]);
            }
        }
        
        // Add completed lab orders with their specific prices
        foreach ($this->labOrders()->where('status', 'completed')->get() as $lab) {
            $rawPrice = $lab->price ?? 0;
            $cleanPrice = is_string($rawPrice) ? str_replace(',', '', $rawPrice) : $rawPrice;
            $labPrice = is_numeric($cleanPrice) ? (float)$cleanPrice : 0.00;
            
            $services->push([
                'type' => 'laboratory',
                'description' => 'Lab: ' . $lab->test_requested,
                'icd_code' => null,
                'unit_price' => $labPrice,
                'quantity' => 1,
                'source' => 'lab_order'
            ]);
        }
        
        // Add medicines - prioritize PatientMedicine records over PharmacyRequest records to avoid duplicates
        $addedMedicines = [];
        
        // First, add patient medicines (final dispensed medicines with accurate pricing)
        foreach ($this->medicines as $medicine) {
            // Determine medicine name (prefer brand name, fall back to generic name)
            $medicineName = $medicine->brand_name ?: $medicine->generic_name;
            
            // Use the medicine's total_price if available, otherwise calculate from unit_price
            $rawPrice = $medicine->total_price ?? ($medicine->unit_price ?? 0);
            $cleanPrice = is_string($rawPrice) ? str_replace(',', '', $rawPrice) : $rawPrice;
            $medicinePrice = is_numeric($cleanPrice) ? (float)$cleanPrice : 0.00;
            
            // Track this medicine to avoid duplicates
            $medicineKey = strtolower($medicineName);
            $addedMedicines[$medicineKey] = true;
            
            $services->push([
                'type' => 'medicine',
                'description' => 'Medicine: ' . ($medicineName ?? 'Medicine'),
                'icd_code' => null,
                'unit_price' => $medicinePrice,
                'quantity' => $medicine->quantity ?? 1,
                'source' => 'patient_medicine'
            ]);
        }
        
        // Then, add pharmacy requests that haven't been added as patient medicines
        foreach ($this->pharmacyRequests()->where('status', 'dispensed')->get() as $pharmacy) {
            // Determine medicine name (prefer brand name, fall back to generic name)
            $medicineName = $pharmacy->brand_name ?: $pharmacy->generic_name;
            $medicineKey = strtolower($medicineName);
            
            // Skip if this medicine was already added from PatientMedicine
            if (isset($addedMedicines[$medicineKey])) {
                continue;
            }
            
            // Get medicine price from pharmacy stocks
            $stock = \App\Models\PharmacyStock::where('generic_name', $medicineName)
                                            ->orWhere('brand_name', $medicineName)
                                            ->first();
            
            // Use the pharmacy request's total_price if available, otherwise calculate from unit_price
            $rawPrice = $pharmacy->total_price ?? ($pharmacy->unit_price ?? ($stock ? $stock->price : 0));
            $cleanPrice = is_string($rawPrice) ? str_replace(',', '', $rawPrice) : $rawPrice;
            $medicinePrice = is_numeric($cleanPrice) ? (float)$cleanPrice : 0.00;
            
            $services->push([
                'type' => 'medicine',
                'description' => 'Medicine: ' . ($medicineName ?? 'Medicine'),
                'icd_code' => null,
                'unit_price' => $medicinePrice,
                'quantity' => $pharmacy->quantity ?? 1,
                'source' => 'pharmacy'
            ]);
        }
        
        return $services->toArray();
    }

    /**
     * Get display name for billing with proper formatting
     */
    public function getDisplayNameAttribute()
    {
        $firstName = ucwords(strtolower(trim($this->first_name ?? '')));
        $middleName = $this->middle_name ? ucwords(strtolower(trim($this->middle_name))) : '';
        $lastName = ucwords(strtolower(trim($this->last_name ?? '')));
        
        return trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);
    }

    // Backward compatibility accessors for admission fields
    public function getRoomNoAttribute()
    {
        return $this->currentAdmission?->room_no;
    }

    public function getAdmissionTypeAttribute()
    {
        return $this->currentAdmission?->admission_type;
    }

    public function getServiceAttribute()
    {
        return $this->currentAdmission?->service;
    }

    public function getDoctorNameAttribute()
    {
        return $this->currentAdmission?->doctor_name;
    }

    public function getDoctorTypeAttribute()
    {
        return $this->currentAdmission?->doctor_type;
    }

    public function getAdmissionDiagnosisAttribute()
    {
        return $this->currentAdmission?->admission_diagnosis;
    }

    public function getStatusAttribute()
    {
        return $this->currentAdmission?->status ?? 'discharged';
    }
}

