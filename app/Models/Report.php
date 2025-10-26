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
    const TYPE_USER_REPORT = 'user_report';
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
            self::TYPE_USER_REPORT => 'User Report',
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
        // Sanitize data to avoid storing sensitive tokens/passwords
        $cleanData = self::sanitizeData($data);

        return self::create([
            'title' => $title,
            'type' => $type,
            'description' => $description,
            'data' => $cleanData,
            'generated_by' => $generatedBy ?? auth()->id(),
            'status' => self::STATUS_COMPLETED,
            'generated_at' => now()
        ]);
    }

    /**
     * Sanitize data arrays to redact sensitive fields before persisting.
     * This will recursively walk arrays and redact any keys that match
     * a configured blacklist (passwords, tokens, etc.).
     */
    private static function sanitizeData($data)
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'password_reset_token',
            'reset_token',
            'auth_token',
        ];

        if (!is_array($data)) {
            return $data;
        }

        $clean = [];
        foreach ($data as $key => $value) {
            // If key is sensitive, replace value with a redaction notice
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $clean[$key] = '[REDACTED]';
                continue;
            }

            // If value is an array, recurse
            if (is_array($value)) {
                $clean[$key] = self::sanitizeData($value);
                continue;
            }

            // Otherwise keep the value (cast to string for safety)
            $clean[$key] = $value;
        }

        return $clean;
    }
}
