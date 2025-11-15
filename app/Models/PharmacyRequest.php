<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyRequest extends Model
{
    use HasFactory;

    protected $table = 'pharmacy_requests';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DISPENSED = 'dispensed';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_STAT = 'stat';

    protected $fillable = [
        'patient_id',
        'admission_id',
        'requested_by',
        'pharmacist_id',
        'patient_name',
        'patient_no',
        'item_code',
        'generic_name',
        'brand_name',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'status',
        'priority',
        'requested_at',
        'started_at',
        'completed_at',
        'dispensed_at',
        'dispensed_by',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    /**
     * Get the patient medicine record if this request was dispensed
     */
    public function patientMedicine()
    {
        return $this->hasOne(PatientMedicine::class);
    }

    // Helper methods
    public function calculateTotalPrice()
    {
        $this->total_price = $this->unit_price * $this->quantity;
        return $this;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDispensed()
    {
        return $this->status === self::STATUS_DISPENSED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeDispensed($query)
    {
        return $query->where('status', self::STATUS_DISPENSED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopeStat($query)
    {
        return $query->where('priority', self::PRIORITY_STAT);
    }
}
