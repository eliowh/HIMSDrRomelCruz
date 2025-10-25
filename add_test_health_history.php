<?php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require 'bootstrap/app.php';

// Boot the application
$app->boot();

use App\Models\Patient;

// Find the first patient
$patient = Patient::first();

if ($patient) {
    // Sample health history data structure based on what the API expects
    $generalHealthHistory = [
        'medical_conditions' => [
            'Hypertension',
            'Type 2 Diabetes',
            'High Cholesterol'
        ],
        'medications' => [
            'Lisinopril 10mg daily',
            'Metformin 500mg twice daily',
            'Atorvastatin 20mg at bedtime'
        ],
        'allergies' => [
            'Penicillin - causes rash',
            'Shellfish - anaphylaxis'
        ],
        'family_history' => [
            'Father - Heart Disease',
            'Mother - Diabetes',
            'Grandmother - Stroke'
        ]
    ];

    $socialHistory = [
        'lifestyle_habits' => [
            'smoking' => 'Former smoker - quit 5 years ago',
            'alcohol' => 'Social drinker - 2-3 drinks per week',
            'exercise' => 'Walks 30 minutes daily',
            'diet' => 'Low sodium, diabetic diet'
        ]
    ];

    // Update the patient with health history
    $patient->update([
        'general_health_history' => $generalHealthHistory,
        'social_history' => $socialHistory
    ]);

    echo "Successfully added health history to patient: {$patient->first_name} {$patient->last_name} (Patient #: {$patient->patient_no})\n";
    echo "General Health History: " . json_encode($generalHealthHistory, JSON_PRETTY_PRINT) . "\n";
    echo "Social History: " . json_encode($socialHistory, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No patients found in the database.\n";
}