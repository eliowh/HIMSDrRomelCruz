<?php
// Quick test of the patient services endpoint
header('Content-Type: application/json');

require 'vendor/autoload.php';
require_once 'bootstrap/app.php';

// Mock the patient_id
$patient_id = $_GET['patient_id'] ?? 1;

try {
    $patient = \App\Models\Patient::with(['labOrders', 'pharmacyRequests'])->findOrFail($patient_id);
    
    $response = [
        'patient' => [
            'id' => $patient->id,
            'name' => $patient->display_name,
            'patient_no' => $patient->patient_no,
            'admission_diagnosis' => $patient->admission_diagnosis
        ],
        'services' => $patient->billable_services
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}