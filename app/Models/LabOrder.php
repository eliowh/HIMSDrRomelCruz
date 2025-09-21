<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'requested_by',
        'lab_tech_id',
        'patient_name',
        'patient_no',
        'test_requested',
        'notes',
        'status',
        'priority',
        'requested_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'results',
        'results_pdf_path'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function labTech()
    {
        return $this->belongsTo(User::class, 'lab_tech_id');
    }
}