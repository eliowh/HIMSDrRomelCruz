<?php

namespace App\Services\FHIR\Resources;

use App\Models\LabOrder;
use App\Services\FHIR\AbstractFhirResource;
use Illuminate\Database\Eloquent\Model;

class FhirObservation extends AbstractFhirResource
{
    /**
     * Get the FHIR resource type
     */
    public function getResourceType(): string
    {
        return 'Observation';
    }

    /**
     * Transform LabOrder model to FHIR Observation resource
     */
    public function transform(Model $model): array
    {
        if (!$model instanceof LabOrder) {
            throw new \InvalidArgumentException('Model must be an instance of LabOrder');
        }

        if (!$this->validate($model)) {
            throw new \InvalidArgumentException('Invalid lab order model');
        }

        $resource = $this->createBaseResource($model);

        // Add identifier
        $resource['identifier'] = [
            $this->createIdentifier(
                (string) $model->id,
                $this->baseUrl . '/lab-order-id',
                'Lab Order ID'
            )
        ];

        // Add status based on lab order status
        $resource['status'] = $this->mapStatus($model->status);

        // Add category - all lab orders are 'laboratory'
        $resource['category'] = [
            [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                        'code' => 'laboratory',
                        'display' => 'Laboratory'
                    ]
                ]
            ]
        ];

        // Add code for the test requested
        $resource['code'] = $this->createCodeableConcept(
            $this->sanitizeCodeValue($model->test_requested),
            $model->test_requested,
            $this->baseUrl . '/lab-test-codes'
        );

        // Add subject (patient reference)
        if ($model->patient_id) {
            $resource['subject'] = $this->createReference(
                "Patient/{$model->patient_id}",
                $model->patient_name
            );
        }

        // Add encounter reference if admission exists
        if ($model->admission_id) {
            $resource['encounter'] = $this->createReference(
                "Encounter/{$model->admission_id}"
            );
        }

        // Add effective datetime
        if ($model->requested_at) {
            $resource['effectiveDateTime'] = $this->formatFhirDateTime($model->requested_at);
        }

        // Add issued datetime (when results were available)
        if ($model->completed_at) {
            $resource['issued'] = $this->formatFhirDateTime($model->completed_at);
        }

        // Add performer (lab technician)
        if ($model->lab_tech_id && $model->labTech) {
            $resource['performer'] = [
                $this->createReference(
                    "Practitioner/{$model->lab_tech_id}",
                    $model->labTech->name ?? 'Lab Technician'
                )
            ];
        }

        // Add results if available
        if ($model->results && $model->status === 'completed') {
            $resource['valueString'] = $model->results;
        } elseif ($model->status !== 'completed') {
            // No value for incomplete tests
            $resource['dataAbsentReason'] = [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/data-absent-reason',
                        'code' => 'not-performed',
                        'display' => 'Not Performed'
                    ]
                ]
            ];
        }

        // Add interpretation if results indicate abnormal values
        if ($model->results && $model->status === 'completed') {
            $resource['interpretation'] = $this->interpretResults($model->results);
        }

        // Add notes/comments
        if ($model->notes) {
            $resource['note'] = [
                [
                    'text' => $model->notes
                ]
            ];
        }

        // Add component for detailed results if structured data exists
        $resource['component'] = $this->buildComponents($model);

        return $resource;
    }

    /**
     * Map lab order status to FHIR observation status
     */
    private function mapStatus(string $status): string
    {
        $statusMap = [
            'requested' => 'registered',
            'in_progress' => 'preliminary', 
            'completed' => 'final',
            'cancelled' => 'cancelled',
            'on_hold' => 'preliminary'
        ];

        return $statusMap[$status] ?? 'registered';
    }

    /**
     * Sanitize code value for FHIR compliance
     */
    private function sanitizeCodeValue(string $value): string
    {
        // Remove special characters and spaces, convert to uppercase
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }

    /**
     * Interpret results to provide basic interpretation codes
     */
    private function interpretResults(string $results): array
    {
        $normalKeywords = ['normal', 'negative', 'within normal limits', 'wnl'];
        $abnormalKeywords = ['abnormal', 'positive', 'elevated', 'high', 'low'];
        
        $resultsLower = strtolower($results);
        
        foreach ($normalKeywords as $keyword) {
            if (strpos($resultsLower, $keyword) !== false) {
                return [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                                'code' => 'N',
                                'display' => 'Normal'
                            ]
                        ]
                    ]
                ];
            }
        }
        
        foreach ($abnormalKeywords as $keyword) {
            if (strpos($resultsLower, $keyword) !== false) {
                return [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                                'code' => 'A',
                                'display' => 'Abnormal'
                            ]
                        ]
                    ]
                ];
            }
        }
        
        // Default to normal if no keywords found
        return [
            [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                        'code' => 'N',
                        'display' => 'Normal'
                    ]
                ]
            ]
        ];
    }

    /**
     * Build components for structured lab results
     */
    private function buildComponents(LabOrder $model): array
    {
        $components = [];

        // Add priority as a component
        if ($model->priority) {
            $components[] = [
                'code' => [
                    'coding' => [
                        [
                            'system' => $this->baseUrl . '/lab-priority',
                            'code' => $model->priority,
                            'display' => ucfirst($model->priority)
                        ]
                    ]
                ],
                'valueString' => ucfirst($model->priority)
            ];
        }

        // Add price as a component if available
        if ($model->price) {
            $components[] = [
                'code' => [
                    'coding' => [
                        [
                            'system' => $this->baseUrl . '/lab-cost',
                            'code' => 'COST',
                            'display' => 'Test Cost'
                        ]
                    ]
                ],
                'valueQuantity' => [
                    'value' => (float) str_replace(',', '', $model->price),
                    'unit' => 'PHP',
                    'system' => 'urn:iso:std:iso:4217',
                    'code' => 'PHP'
                ]
            ];
        }

        return $components;
    }

    /**
     * Validate LabOrder model
     */
    public function validate(Model $model): bool
    {
        if (!$model instanceof LabOrder) {
            return false;
        }

        // Basic validation - must have patient, test requested, and requested date
        return !empty($model->patient_id) && 
               !empty($model->test_requested) && 
               !empty($model->requested_at);
    }
}