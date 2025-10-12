<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabAnalysis extends Model
{
    protected $fillable = [
        'lab_order_id',
        'doctor_id',
        'clinical_notes',
        'recommendations',
        'analyzed_at'
    ];

    protected $casts = [
        'analyzed_at' => 'datetime'
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
