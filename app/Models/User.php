<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'title',
        'email',
        'password',
        'role',
        'password_reset_token',
        'license_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The patients that belong to the doctor.
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'doctor_id', 'patient_id');
    }

    /**
     * Check if user is a doctor.
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Check if user is a nurse.
     */
    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    /**
     * Check if user is inventory role.
     */
    public function isInventory(): bool
    {
        return $this->role === 'inventory';
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is pharmacy role.
     */
    public function isPharmacy(): bool
    {
        return $this->role === 'pharmacy';
    }

    /**
     * Check if user is billing role.
     */
    public function isBilling(): bool
    {
        return $this->role === 'billing';
    }

    /**
     * Check if user is lab technician role.
     */
    public function isLabTechnician(): bool
    {
        return $this->role === 'lab_technician';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get the user's full name with professional title.
     * Format: "Name, Title" (e.g., "John Doe, RMT")
     */
    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->title)) {
            return $this->name . ', ' . $this->title;
        }
        return $this->name;
    }

    /**
     * Get the user's full name with professional title.
     * This is an alias for the display_name attribute.
     */
    public function getFormattedName(): string
    {
        return $this->display_name;
    }
}
