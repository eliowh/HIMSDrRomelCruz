<div class="patient-details-container">
    <div class="patient-info-grid">
        <div class="patient-info-section">
            <h4><i class="fas fa-user"></i> Personal Information</h4>
            <div class="info-row">
                <span class="label">Patient Number:</span>
                <span class="value">{{ $patient->patient_no ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Full Name:</span>
                <span class="value">{{ ($patient->first_name ?? '') . ' ' . ($patient->middle_name ?? '') . ' ' . ($patient->last_name ?? '') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date of Birth:</span>
                <span class="value">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('F j, Y') : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Age:</span>
                @php
                    $age = 'N/A';
                    if ($patient->date_of_birth) {
                        $dob = \Carbon\Carbon::parse($patient->date_of_birth);
                        $diff = $dob->diff(now());
                        $parts = [];
                        if ($diff->y) { $parts[] = $diff->y . 'y'; }
                        if ($diff->m) { $parts[] = $diff->m . 'm'; }
                        if ($diff->d && $diff->y === 0) { $parts[] = $diff->d . 'd'; }
                        $age = $parts ? implode(' ', $parts) : '0y';
                    }
                @endphp
                    @php
                        $ageYears = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->diffInYears(now()) : null;
                    @endphp
                    <span class="value">{{ $ageYears !== null ? $ageYears.' years' : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Gender:</span>
                <span class="value">{{ ucfirst($patient->sex ?? 'N/A') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Marital Status:</span>
                <span class="value">{{ ucfirst($patient->marital_status ?? 'N/A') }}</span>
            </div>
        </div>

        <div class="patient-info-section">
            <h4><i class="fas fa-address-card"></i> Contact Information</h4>
            <div class="info-row">
                <span class="label">Contact Number:</span>
                <span class="value">{{ $patient->contact_number ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $patient->email ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Address:</span>
                <span class="value">{{ $patient->address ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Emergency Contact:</span>
                <span class="value">{{ $patient->emergency_contact_name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Emergency Phone:</span>
                <span class="value">{{ $patient->emergency_contact_phone ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="patient-info-section">
            <h4><i class="fas fa-hospital"></i> Medical Information</h4>
            <div class="info-row">
                <span class="label">Blood Type:</span>
                <span class="value">{{ $patient->blood_type ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Allergies:</span>
                <span class="value">{{ $patient->allergies ?? 'None specified' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Medical History:</span>
                <span class="value">{{ $patient->medical_history ?? 'None specified' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Current Medications:</span>
                <span class="value">{{ $patient->current_medications ?? 'None specified' }}</span>
            </div>
        </div>

        <div class="patient-info-section">
            <h4><i class="fas fa-bed"></i> Admission Information</h4>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value">
                    <span class="status-badge status-{{ strtolower($patient->status ?? 'unknown') }}">
                        {{ ucfirst($patient->status ?? 'Unknown') }}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Room Number:</span>
                <span class="value">{{ $patient->room_no ?? 'Not assigned' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Admission Date:</span>
                <span class="value">{{ $patient->admission_date ? \Carbon\Carbon::parse($patient->admission_date)->format('F j, Y g:i A') : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Discharge Date:</span>
                <span class="value">{{ $patient->discharge_date ? \Carbon\Carbon::parse($patient->discharge_date)->format('F j, Y g:i A') : 'Not discharged' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Admitting Doctor:</span>
                <span class="value">{{ $patient->admitting_doctor ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Diagnosis:</span>
                <span class="value">{{ $patient->diagnosis ?? 'Pending' }}</span>
            </div>
        </div>

        @if($patient->notes)
        <div class="patient-info-section full-width">
            <h4><i class="fas fa-sticky-note"></i> Additional Notes</h4>
            <div class="notes-content">
                {{ $patient->notes }}
            </div>
        </div>
        @endif
    </div>

    <div class="patient-details-footer">
        <div class="footer-info">
            <span><i class="fas fa-calendar-plus"></i> Created: {{ $patient->created_at ? \Carbon\Carbon::parse($patient->created_at)->format('F j, Y g:i A') : 'N/A' }}</span>
            <span><i class="fas fa-calendar-edit"></i> Last Updated: {{ $patient->updated_at ? \Carbon\Carbon::parse($patient->updated_at)->format('F j, Y g:i A') : 'N/A' }}</span>
        </div>
    </div>
</div>

<style>
.patient-details-container {
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.patient-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.patient-info-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #667eea;
}

.patient-info-section.full-width {
    grid-column: 1 / -1;
}

.patient-info-section h4 {
    color: #333;
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.patient-info-section h4 i {
    color: #667eea;
    font-size: 18px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-row .label {
    font-weight: 600;
    color: #495057;
    flex: 0 0 40%;
    font-size: 14px;
}

.info-row .value {
    color: #212529;
    flex: 1;
    text-align: right;
    font-size: 14px;
    word-break: break-word;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-discharged {
    background: #cce5ff;
    color: #004085;
    border: 1px solid #b3d7ff;
}

.status-deceased {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-unknown {
    background: #e2e3e5;
    color: #495057;
    border: 1px solid #d6d8db;
}

.notes-content {
    background: white;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    line-height: 1.6;
    color: #495057;
    white-space: pre-wrap;
}

.patient-details-footer {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px solid #e9ecef;
}

.footer-info {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #6c757d;
}

.footer-info span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.footer-info i {
    color: #667eea;
}

/* Responsive Design */
@media (max-width: 768px) {
    .patient-info-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-row .value {
        text-align: left;
    }
    
    .footer-info {
        flex-direction: column;
        gap: 8px;
    }
    
    .patient-info-section {
        padding: 15px;
    }
}

/* Print Styles */
@media print {
    .patient-details-container {
        color: black !important;
    }
    
    .patient-info-section {
        background: white !important;
        border: 1px solid #ccc !important;
    }
    
    .status-badge {
        border: 1px solid #000 !important;
    }
}
</style>