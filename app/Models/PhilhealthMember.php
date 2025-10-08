<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PhilhealthMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'philhealth_number',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'member_type',
        'category',
        'premium_amount',
        'effectivity_date',
        'expiry_date',
        'employer',
        'address',
        'status'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'effectivity_date' => 'date',
        'expiry_date' => 'date',
        'premium_amount' => 'decimal:2'
    ];

    const MEMBER_TYPES = [
        'Active' => 'Active Member',
        'Lifetime' => 'Lifetime Member',
        'Indigent' => 'Indigent Member'
    ];

    const CATEGORIES = [
        'Direct Contributor' => 'Direct Contributor',
        'Indirect Contributor' => 'Indirect Contributor',
        'Senior Citizen' => 'Senior Citizen',
        'PWD Member' => 'Person with Disability',
        'Sponsored' => 'Sponsored Member'
    ];

    const MEMBERSHIP_STATUS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended'
    ];

    public function getFullNameAttribute()
    {
        $middleInitial = $this->middle_name ? strtoupper(substr($this->middle_name, 0, 1)) . '.' : '';
        return trim($this->first_name . ' ' . $middleInitial . ' ' . $this->last_name);
    }

    public function isActiveAndCovered()
    {
        $now = Carbon::now()->toDateString();
        
        return $this->status === 'active' &&
               $this->effectivity_date <= $now &&
               $this->expiry_date >= $now;
    }

    public function getFormattedMemberType()
    {
        return self::MEMBER_TYPES[$this->member_type] ?? $this->member_type;
    }

    public function getFormattedMembershipStatus()
    {
        return self::MEMBERSHIP_STATUS[$this->status] ?? $this->status;
    }

    // Check if member is eligible for coverage
    public function isEligibleForCoverage()
    {
        return $this->isActiveAndCovered();
    }

    // Check if patient matches this PhilHealth member
    public static function findByPatient(Patient $patient)
    {
        return self::where('first_name', 'LIKE', '%' . $patient->firstName . '%')
                  ->where('last_name', 'LIKE', '%' . $patient->lastName . '%')
                  ->where('birth_date', $patient->dateOfBirth)
                  ->first();
    }
}
