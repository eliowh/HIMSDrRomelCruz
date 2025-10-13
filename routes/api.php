<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FHIR\FhirController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| FHIR API Routes
|--------------------------------------------------------------------------
| FHIR R4 compliant API endpoints for healthcare data interoperability
| These routes follow FHIR URL conventions and return FHIR-compliant JSON
|
*/

Route::prefix('fhir')->middleware('fhir')->group(function () {
    
    // Handle CORS preflight requests
    Route::options('{any}', [FhirController::class, 'options'])->where('any', '.*');
    
    // FHIR Metadata/Capability Statement
    Route::get('metadata', [FhirController::class, 'metadata'])
        ->name('fhir.metadata');

    // Patient Resource Routes
    Route::prefix('Patient')->group(function () {
        // Search patients
        Route::get('/', [FhirController::class, 'searchPatients'])
            ->name('fhir.patient.search');
        
        // Get specific patient
        Route::get('{id}', [FhirController::class, 'getPatient'])
            ->where('id', '[0-9]+')
            ->name('fhir.patient.read');
        
        // Get patient with all related resources ($everything operation)
        Route::get('{id}/$everything', [FhirController::class, 'getPatientEverything'])
            ->where('id', '[0-9]+')
            ->name('fhir.patient.everything');
    });

    // Encounter Resource Routes  
    Route::prefix('Encounter')->group(function () {
        // Get specific encounter
        Route::get('{id}', [FhirController::class, 'getEncounter'])
            ->where('id', '[0-9]+')
            ->name('fhir.encounter.read');
    });

    // Observation Resource Routes
    Route::prefix('Observation')->group(function () {
        // Get specific observation
        Route::get('{id}', [FhirController::class, 'getObservation'])
            ->where('id', '[0-9]+')
            ->name('fhir.observation.read');
    });

    // MedicationStatement Resource Routes
    Route::prefix('MedicationStatement')->group(function () {
        // Get specific medication statement (supports both pm-{id} and pr-{id} formats)
        Route::get('{typeId}', [FhirController::class, 'getMedicationStatement'])
            ->where('typeId', '(pm|pr)-[0-9]+')
            ->name('fhir.medication-statement.read');
    });

    // FHIR Operations
    // Validate resource
    Route::post('$validate', [FhirController::class, 'validate'])
        ->name('fhir.validate');
    
});

/*
|--------------------------------------------------------------------------
| General API Routes
|--------------------------------------------------------------------------
| Non-FHIR API routes can be added here
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Location proxy endpoints to avoid CORS with public PSGC API
use App\Http\Controllers\Api\LocationController;

Route::prefix('locations')->group(function () {
    Route::get('provinces', [LocationController::class, 'provinces']);
    Route::get('cities', [LocationController::class, 'cities']);
});