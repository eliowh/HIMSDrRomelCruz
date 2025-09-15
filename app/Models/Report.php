<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'data',
        'generated_by',
        'status',
        'generated_at'
    ];

    protected $casts = [
        'data' => 'array',
        'generated_at' => 'datetime'
    ];

    // Report types
    const TYPE_USER_ACTIVITY = 'user_activity';
    const TYPE_SYSTEM_LOG = 'system_log';
    const TYPE_LOGIN_REPORT = 'login_report';
    const TYPE_USER_REGISTRATION = 'user_registration';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_CUSTOM = 'custom';

    // Report statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the user who generated the report
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get formatted type name
     */
    public function getFormattedTypeAttribute()
    {
        return match($this->type) {
            self::TYPE_USER_ACTIVITY => 'User Activity',
            self::TYPE_SYSTEM_LOG => 'System Log',
            self::TYPE_LOGIN_REPORT => 'Login Report',
            self::TYPE_USER_REGISTRATION => 'User Registration',
            self::TYPE_PASSWORD_RESET => 'Password Reset',
            self::TYPE_CUSTOM => 'Custom Report',
            default => ucfirst(str_replace('_', ' ', $this->type))
        };
    }

    /**
     * Scope for filtering by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Create a new report log entry
     */
    public static function log($title, $type, $description = null, $data = [], $generatedBy = null)
    {
        return self::create([
            'title' => $title,
            'type' => $type,
            'description' => $description,
            'data' => $data,
            'generated_by' => $generatedBy ?? auth()->id(),
            'status' => self::STATUS_COMPLETED,
            'generated_at' => now()
        ]);
    }
}
