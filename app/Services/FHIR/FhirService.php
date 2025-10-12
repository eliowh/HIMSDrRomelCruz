<?php

namespace App\Services\FHIR;

use App\Models\Patient;
use App\Models\Admission;
use App\Models\LabOrder;
use App\Models\PatientMedicine;
use App\Models\PharmacyRequest;
use App\Services\FHIR\Resources\FhirPatient;
use App\Services\FHIR\Resources\FhirEncounter;
use App\Services\FHIR\Resources\FhirObservation;
use App\Services\FHIR\Resources\FhirMedicationStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FhirService
{
    private array $transformers;
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url');
        $this->initializeTransformers();
    }

    /**
     * Initialize FHIR resource transformers
     */
    private function initializeTransformers(): void
    {
        $this->transformers = [
            Patient::class => new FhirPatient(),
            Admission::class => new FhirEncounter(),
            LabOrder::class => new FhirObservation(),
            PatientMedicine::class => new FhirMedicationStatement(),
            PharmacyRequest::class => new FhirMedicationStatement(),
        ];
    }

    /**
     * Transform a single model to FHIR resource
     */
    public function transformToFhir(Model $model): array
    {
        $modelClass = get_class($model);
        
        if (!isset($this->transformers[$modelClass])) {
            throw new \InvalidArgumentException("No FHIR transformer available for model: {$modelClass}");
        }

        $transformer = $this->transformers[$modelClass];
        return $transformer->transform($model);
    }

    /**
     * Transform a collection of models to FHIR resources
     */
    public function transformCollectionToFhir(Collection $models): array
    {
        $resources = [];
        
        foreach ($models as $model) {
            try {
                $resources[] = $this->transformToFhir($model);
            } catch (\Exception $e) {
                // Log error but continue processing other models
                \Log::warning("Failed to transform model to FHIR: " . $e->getMessage(), [
                    'model_class' => get_class($model),
                    'model_id' => $model->id ?? 'unknown'
                ]);
            }
        }
        
        return $resources;
    }

    /**
     * Get patient FHIR resource by ID
     */
    public function getPatient(int $patientId): ?array
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return null;
        }

        return $this->transformToFhir($patient);
    }

    /**
     * Get patient's complete FHIR bundle (patient + related resources)
     */
    public function getPatientBundle(int $patientId): array
    {
        $patient = Patient::with([
            'admissions',
            'labOrders',
            'medicines',
            'pharmacyRequests'
        ])->find($patientId);

        if (!$patient) {
            return $this->createErrorBundle('Patient not found', 'not-found');
        }

        return $this->createPatientBundle($patient);
    }

    /**
     * Get encounter FHIR resource by ID
     */
    public function getEncounter(int $admissionId): ?array
    {
        $admission = Admission::with('patient')->find($admissionId);
        
        if (!$admission) {
            return null;
        }

        return $this->transformToFhir($admission);
    }

    /**
     * Get observation FHIR resource by ID
     */
    public function getObservation(int $labOrderId): ?array
    {
        $labOrder = LabOrder::with(['patient', 'labTech'])->find($labOrderId);
        
        if (!$labOrder) {
            return null;
        }

        return $this->transformToFhir($labOrder);
    }

    /**
     * Get medication statement FHIR resource by ID and type
     */
    public function getMedicationStatement(int $id, string $type = 'patient_medicine'): ?array
    {
        if ($type === 'patient_medicine') {
            $model = PatientMedicine::with('patient')->find($id);
        } elseif ($type === 'pharmacy_request') {
            $model = PharmacyRequest::with('patient')->find($id);
        } else {
            return null;
        }

        if (!$model) {
            return null;
        }

        return $this->transformToFhir($model);
    }

    /**
     * Search patients by name, patient number, or other criteria
     */
    public function searchPatients(array $criteria): array
    {
        $query = Patient::query();

        // Apply search criteria
        if (isset($criteria['name'])) {
            $name = $criteria['name'];
            $query->where(function($q) use ($name) {
                $q->where('first_name', 'LIKE', "%{$name}%")
                  ->orWhere('last_name', 'LIKE', "%{$name}%")
                  ->orWhere('middle_name', 'LIKE', "%{$name}%");
            });
        }

        if (isset($criteria['patient_no'])) {
            $query->where('patient_no', $criteria['patient_no']);
        }

        if (isset($criteria['birthdate'])) {
            $query->where('date_of_birth', $criteria['birthdate']);
        }

        if (isset($criteria['gender'])) {
            $query->where('sex', $criteria['gender']);
        }

        // Apply pagination
        $limit = $criteria['_count'] ?? 20;
        $offset = $criteria['_offset'] ?? 0;
        
        $patients = $query->offset($offset)->limit($limit)->get();
        
        return $this->createSearchBundle($patients, 'Patient', $criteria);
    }

    /**
     * Create a patient bundle with all related resources
     */
    private function createPatientBundle(Patient $patient): array
    {
        $entries = [];

        // Add patient resource
        $entries[] = [
            'fullUrl' => "{$this->baseUrl}/Patient/{$patient->id}",
            'resource' => $this->transformToFhir($patient)
        ];

        // Add encounters (admissions)
        foreach ($patient->admissions as $admission) {
            $entries[] = [
                'fullUrl' => "{$this->baseUrl}/Encounter/{$admission->id}",
                'resource' => $this->transformToFhir($admission)
            ];
        }

        // Add observations (lab orders)
        foreach ($patient->labOrders as $labOrder) {
            $entries[] = [
                'fullUrl' => "{$this->baseUrl}/Observation/{$labOrder->id}",
                'resource' => $this->transformToFhir($labOrder)
            ];
        }

        // Add medication statements (patient medicines)
        foreach ($patient->medicines as $medicine) {
            $entries[] = [
                'fullUrl' => "{$this->baseUrl}/MedicationStatement/pm-{$medicine->id}",
                'resource' => $this->transformToFhir($medicine)
            ];
        }

        // Add medication statements (pharmacy requests)
        foreach ($patient->pharmacyRequests as $pharmacy) {
            $entries[] = [
                'fullUrl' => "{$this->baseUrl}/MedicationStatement/pr-{$pharmacy->id}",
                'resource' => $this->transformToFhir($pharmacy)
            ];
        }

        return $this->createBundle('collection', $entries, "Patient/{$patient->id} Bundle");
    }

    /**
     * Create a search results bundle
     */
    private function createSearchBundle(Collection $resources, string $resourceType, array $searchCriteria): array
    {
        $entries = [];

        foreach ($resources as $resource) {
            $fhirResource = $this->transformToFhir($resource);
            $entries[] = [
                'fullUrl' => "{$this->baseUrl}/{$resourceType}/{$resource->id}",
                'resource' => $fhirResource,
                'search' => [
                    'mode' => 'match'
                ]
            ];
        }

        $bundle = $this->createBundle('searchset', $entries, "{$resourceType} Search Results");
        
        // Add search parameters to bundle
        $bundle['total'] = count($entries);
        
        return $bundle;
    }

    /**
     * Create a FHIR Bundle resource
     */
    private function createBundle(string $type, array $entries, string $title = null): array
    {
        $bundle = [
            'resourceType' => 'Bundle',
            'id' => uniqid('bundle-'),
            'meta' => [
                'lastUpdated' => now()->toISOString()
            ],
            'type' => $type,
            'total' => count($entries),
            'entry' => $entries
        ];

        if ($title) {
            $bundle['title'] = $title;
        }

        return $bundle;
    }

    /**
     * Create an error bundle
     */
    private function createErrorBundle(string $message, string $code = 'unknown'): array
    {
        return [
            'resourceType' => 'Bundle',
            'id' => uniqid('error-bundle-'),
            'meta' => [
                'lastUpdated' => now()->toISOString()
            ],
            'type' => 'collection',
            'total' => 0,
            'entry' => [],
            'issue' => [
                [
                    'severity' => 'error',
                    'code' => $code,
                    'details' => [
                        'text' => $message
                    ]
                ]
            ]
        ];
    }

    /**
     * Validate FHIR resource against basic constraints
     */
    public function validateResource(array $resource): array
    {
        $errors = [];

        // Check required fields
        if (!isset($resource['resourceType'])) {
            $errors[] = 'Missing required field: resourceType';
        }

        if (!isset($resource['id'])) {
            $errors[] = 'Missing required field: id';
        }

        // Resource-specific validation
        switch ($resource['resourceType'] ?? '') {
            case 'Patient':
                if (!isset($resource['name']) || empty($resource['name'])) {
                    $errors[] = 'Patient must have at least one name';
                }
                break;
                
            case 'Encounter':
                if (!isset($resource['subject'])) {
                    $errors[] = 'Encounter must have a subject (patient)';
                }
                if (!isset($resource['status'])) {
                    $errors[] = 'Encounter must have a status';
                }
                break;
                
            case 'Observation':
                if (!isset($resource['subject'])) {
                    $errors[] = 'Observation must have a subject (patient)';
                }
                if (!isset($resource['code'])) {
                    $errors[] = 'Observation must have a code';
                }
                if (!isset($resource['status'])) {
                    $errors[] = 'Observation must have a status';
                }
                break;
                
            case 'MedicationStatement':
                if (!isset($resource['subject'])) {
                    $errors[] = 'MedicationStatement must have a subject (patient)';
                }
                if (!isset($resource['medicationCodeableConcept'])) {
                    $errors[] = 'MedicationStatement must have medication information';
                }
                break;
        }

        return $errors;
    }

    /**
     * Get available FHIR resource types
     */
    public function getAvailableResourceTypes(): array
    {
        return ['Patient', 'Encounter', 'Observation', 'MedicationStatement'];
    }

    /**
     * Get FHIR capability statement
     */
    public function getCapabilityStatement(): array
    {
        return [
            'resourceType' => 'CapabilityStatement',
            'id' => 'hims-fhir-server',
            'name' => 'HIMS FHIR Server',
            'title' => 'Hospital Information Management System FHIR Server',
            'status' => 'active',
            'experimental' => false,
            'date' => '2025-10-12',
            'publisher' => 'Dr. Romel Cruz Hospital',
            'description' => 'FHIR server for Hospital Information Management System',
            'kind' => 'instance',
            'fhirVersion' => '4.0.1',
            'format' => ['json'],
            'implementation' => [
                'description' => 'Dr. Romel Cruz Hospital FHIR Server',
                'url' => rtrim($this->baseUrl, '/') . '/api/fhir'
            ],
            'rest' => [
                [
                    'mode' => 'server',
                    'resource' => [
                        [
                            'type' => 'Patient',
                            'interaction' => [
                                ['code' => 'read'],
                                ['code' => 'search-type']
                            ],
                            'searchParam' => [
                                ['name' => 'name', 'type' => 'string'],
                                ['name' => 'birthdate', 'type' => 'date'],
                                ['name' => 'gender', 'type' => 'token']
                            ]
                        ],
                        [
                            'type' => 'Encounter',
                            'interaction' => [
                                ['code' => 'read']
                            ]
                        ],
                        [
                            'type' => 'Observation',
                            'interaction' => [
                                ['code' => 'read']
                            ]
                        ],
                        [
                            'type' => 'MedicationStatement',
                            'interaction' => [
                                ['code' => 'read']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}