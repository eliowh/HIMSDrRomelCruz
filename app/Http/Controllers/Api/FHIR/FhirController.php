<?php

namespace App\Http\Controllers\Api\FHIR;

use App\Http\Controllers\Controller;
use App\Services\FHIR\FhirService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirController extends Controller
{
    private FhirService $fhirService;

    public function __construct(FhirService $fhirService)
    {
        $this->fhirService = $fhirService;
    }

    /**
     * Get FHIR capability statement (metadata)
     * GET /api/fhir/metadata
     */
    public function metadata(): JsonResponse
    {
        try {
            $capability = $this->fhirService->getCapabilityStatement();
            return $this->fhirResponse($capability);
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving capability statement', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Patient resource by ID
     * GET /api/fhir/Patient/{id}
     */
    public function getPatient(Request $request, int $id): JsonResponse
    {
        try {
            $patient = $this->fhirService->getPatient($id);

            if (!$patient) {
                return $this->errorResponse('Patient not found', 'not-found', Response::HTTP_NOT_FOUND);
            }

            return $this->fhirResponse($patient);
        } catch (\Exception $e) {
            \Log::error('FHIR Patient retrieval error', [
                'patient_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error retrieving patient', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Patient bundle with all related resources
     * GET /api/fhir/Patient/{id}/$everything
     */
    public function getPatientEverything(Request $request, int $id): JsonResponse
    {
        try {
            $bundle = $this->fhirService->getPatientBundle($id);
            return $this->fhirResponse($bundle);
        } catch (\Exception $e) {
            \Log::error('FHIR Patient bundle retrieval error', [
                'patient_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error retrieving patient bundle', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search Patient resources
     * GET /api/fhir/Patient
     */
    public function searchPatients(Request $request): JsonResponse
    {
        try {
            $criteria = $this->extractSearchCriteria($request, [
                'name' => 'string',
                'patient_no' => 'token',
                'birthdate' => 'date',
                'gender' => 'token',
                '_count' => 'number',
                '_offset' => 'number'
            ]);

            $bundle = $this->fhirService->searchPatients($criteria);
            return $this->fhirResponse($bundle);
        } catch (\Exception $e) {
            \Log::error('FHIR Patient search error', [
                'criteria' => $request->query(),
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error searching patients', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Encounter resource by ID
     * GET /api/fhir/Encounter/{id}
     */
    public function getEncounter(Request $request, int $id): JsonResponse
    {
        try {
            $encounter = $this->fhirService->getEncounter($id);

            if (!$encounter) {
                return $this->errorResponse('Encounter not found', 'not-found', Response::HTTP_NOT_FOUND);
            }

            return $this->fhirResponse($encounter);
        } catch (\Exception $e) {
            \Log::error('FHIR Encounter retrieval error', [
                'encounter_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error retrieving encounter', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Observation resource by ID
     * GET /api/fhir/Observation/{id}
     */
    public function getObservation(Request $request, int $id): JsonResponse
    {
        try {
            $observation = $this->fhirService->getObservation($id);

            if (!$observation) {
                return $this->errorResponse('Observation not found', 'not-found', Response::HTTP_NOT_FOUND);
            }

            return $this->fhirResponse($observation);
        } catch (\Exception $e) {
            \Log::error('FHIR Observation retrieval error', [
                'observation_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error retrieving observation', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get MedicationStatement resource by ID
     * GET /api/fhir/MedicationStatement/{type}-{id}
     */
    public function getMedicationStatement(Request $request, string $typeId): JsonResponse
    {
        try {
            // Parse type and ID from the combined identifier
            if (strpos($typeId, 'pm-') === 0) {
                $type = 'patient_medicine';
                $id = (int) substr($typeId, 3);
            } elseif (strpos($typeId, 'pr-') === 0) {
                $type = 'pharmacy_request';
                $id = (int) substr($typeId, 3);
            } else {
                return $this->errorResponse('Invalid MedicationStatement identifier format', 'invalid', Response::HTTP_BAD_REQUEST);
            }

            $medicationStatement = $this->fhirService->getMedicationStatement($id, $type);

            if (!$medicationStatement) {
                return $this->errorResponse('MedicationStatement not found', 'not-found', Response::HTTP_NOT_FOUND);
            }

            return $this->fhirResponse($medicationStatement);
        } catch (\Exception $e) {
            \Log::error('FHIR MedicationStatement retrieval error', [
                'type_id' => $typeId,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error retrieving medication statement', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle CORS preflight requests
     * OPTIONS /api/fhir/*
     */
    public function options(Request $request): JsonResponse
    {
        return response()->json([], 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With'
        ]);
    }

    /**
     * Validate FHIR resource
     * POST /api/fhir/$validate
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $resource = $request->json()->all();
            
            if (empty($resource)) {
                return $this->errorResponse('No resource provided for validation', 'invalid', Response::HTTP_BAD_REQUEST);
            }

            $errors = $this->fhirService->validateResource($resource);

            if (empty($errors)) {
                return $this->operationOutcome('information', 'informational', 'Resource is valid');
            } else {
                return $this->operationOutcome('error', 'structure', 'Resource validation failed', $errors, Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            \Log::error('FHIR resource validation error', [
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Error validating resource', 'exception', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Extract search criteria from request
     */
    private function extractSearchCriteria(Request $request, array $supportedParams): array
    {
        $criteria = [];

        foreach ($supportedParams as $param => $type) {
            if ($request->has($param)) {
                $value = $request->query($param);
                
                // Type-specific processing
                switch ($type) {
                    case 'number':
                        $criteria[$param] = (int) $value;
                        break;
                    case 'date':
                        // Handle FHIR date formats
                        $criteria[$param] = $value;
                        break;
                    case 'token':
                    case 'string':
                    default:
                        $criteria[$param] = $value;
                        break;
                }
            }
        }

        return $criteria;
    }

    /**
     * Create FHIR-compliant JSON response
     */
    private function fhirResponse(array $resource, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json($resource, $status, [
            'Content-Type' => 'application/fhir+json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With'
        ]);
    }

    /**
     * Create FHIR-compliant error response using OperationOutcome
     */
    private function errorResponse(string $message, string $code = 'unknown', int $status = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $operationOutcome = [
            'resourceType' => 'OperationOutcome',
            'id' => uniqid('error-'),
            'meta' => [
                'lastUpdated' => now()->toISOString()
            ],
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

        return $this->fhirResponse($operationOutcome, $status);
    }

    /**
     * Create FHIR OperationOutcome response
     */
    private function operationOutcome(string $severity, string $code, string $message, array $details = [], int $status = Response::HTTP_OK): JsonResponse
    {
        $issue = [
            'severity' => $severity,
            'code' => $code,
            'details' => [
                'text' => $message
            ]
        ];

        if (!empty($details)) {
            $issue['diagnostics'] = implode('; ', $details);
        }

        $operationOutcome = [
            'resourceType' => 'OperationOutcome',
            'id' => uniqid('outcome-'),
            'meta' => [
                'lastUpdated' => now()->toISOString()
            ],
            'issue' => [$issue]
        ];

        return $this->fhirResponse($operationOutcome, $status);
    }
}