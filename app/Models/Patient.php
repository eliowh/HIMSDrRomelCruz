<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($patient) {
            if (empty($patient->patient_no)) {
                // get current max patient_no and increment, start at 250001
                $max = (int) DB::table('patients')->max('patient_no');
                $patient->patient_no = ($max >= 250001) ? ($max + 1) : 250001;
            }
        });
    }
}
?>