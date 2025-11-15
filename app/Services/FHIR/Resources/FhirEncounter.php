<?php

namespace App\Services\FHIR\Resources;

use App\Models\Admission;
use App\Services\FHIR\AbstractFhirResource;
use Illuminate\Database\Eloquent\Model;

class FhirEncounter extends AbstractFhirResource
{
    /**
     * Get the FHIR resource type
     */
    public function getResourceType(): string
    {
        return 'Encounter';
    }

    /**
     * Transform Admission model to FHIR Encounter resource
     */
    public function transform(Model $model): array
    {
        if (!$model instanceof Admission) {
            throw new \InvalidArgumentException('Model must be an instance of Admission');
        }

        if (!$this->validate($model)) {
            throw new \InvalidArgumentException('Invalid admission model');
        }

        $resource = $this->createBaseResource($model);

        // Add identifier
        if ($model->admission_number) {
            $resource['identifier'] = [
                $this->createIdentifier(
                    $model->admission_number,
                    $this->baseUrl . '/admission-number',
                    'Admission Number'
                )
            ];
        }

        // Add status
        $resource['status'] = $this->mapStatus($model->status);

        // Add class - mapping admission type to encounter class
        $resource['class'] = $this->mapEncounterClass($model->admission_type);

        // Add type
        if ($model->admission_type) {
            $resource['type'] = [
                $this->createCodeableConcept(
                    $model->admission_type,
                    ucfirst($model->admission_type),
                    $this->baseUrl . '/admission-type'
                )
            ];
        }

        // Add service type
        if ($model->service) {
            $resource['serviceType'] = $this->createCodeableConcept(
                $model->service,
                $model->service,
                $this->baseUrl . '/service-type'
            );
        }

        // Add subject (patient reference)
        if ($model->patient_id) {
            $resource['subject'] = $this->createReference(
                "Patient/{$model->patient_id}",
                $model->patient ? $model->patient->display_name : null
            );
        }

        // Add participant (doctor)
        if ($model->doctor_name) {
            $resource['participant'] = [
                [
                    'type' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                    'code' => 'ATND',
                                    'display' => 'attender'
                                ]
                            ]
                        ]
                    ],
                    'individual' => [
                        'display' => $model->doctor_name
                    ]
                ]
            ];
        }

        // Add period
        $resource['period'] = $this->buildPeriod($model);

        // Add reason code (diagnosis)
        if ($model->admission_diagnosis || $model->final_diagnosis) {
            $resource['reasonCode'] = $this->buildReasonCodes($model);
        }

        // Add hospitalization details
        $resource['hospitalization'] = $this->buildHospitalization($model);

        // Add location (room)
        if ($model->room_no) {
            $resource['location'] = [
                [
                    'location' => [
                        'display' => "Room {$model->room_no}"
                    ],
                    'status' => $model->status === 'active' ? 'active' : 'completed'
                ]
            ];
        }

        return $resource;
    }

    /**
     * Map admission status to FHIR encounter status
     */
    private function mapStatus(string $status): string
    {
        $statusMap = [
            'active' => 'in-progress',
            'discharged' => 'finished',
            'cancelled' => 'cancelled',
            'on-hold' => 'onhold'
        ];

        return $statusMap[$status] ?? 'unknown';
    }

    /**
     * Map admission type to FHIR encounter class
     */
    private function mapEncounterClass(string $admissionType = null): array
    {
        $classMap = [
            'inpatient' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'IMP',
                'display' => 'inpatient encounter'
            ],
            'outpatient' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'AMB',
                'display' => 'ambulatory'
            ],
            'emergency' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'EMER',
                'display' => 'emergency'
            ]
        ];

        return $classMap[$admissionType] ?? [
            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
            'code' => 'IMP',
            'display' => 'inpatient encounter'
        ];
    }

    /**
     * Build encounter period
     */
    private function buildPeriod(Admission $model): array
    {
        $period = [];

        if ($model->admission_date) {
            $period['start'] = $this->formatFhirDateTime($model->admission_date);
        }

        if ($model->discharge_date) {
            $period['end'] = $this->formatFhirDateTime($model->discharge_date);
        }

        return $period;
    }

    /**
     * Build reason codes for diagnoses
     */
    private function buildReasonCodes(Admission $model): array
    {
        $reasonCodes = [];

        // Add admission diagnosis
        if ($model->admission_diagnosis) {
            $reasonCodes[] = $this->createCodeableConcept(
                $model->admission_diagnosis,
                $model->admission_diagnosis,
                'http://hl7.org/fhir/sid/icd-10'
            );
        }

        // Add final diagnosis if different from admission diagnosis
        if ($model->final_diagnosis && $model->final_diagnosis !== $model->admission_diagnosis) {
            $reasonCodes[] = $this->createCodeableConcept(
                $model->final_diagnosis,
                $model->final_diagnosis_description ?? $model->final_diagnosis,
                'http://hl7.org/fhir/sid/icd-10'
            );
        }

        return $reasonCodes;
    }

    /**
     * Build hospitalization details
     */
    private function buildHospitalization(Admission $model): array
    {
        $hospitalization = [];

        // Add admission source if available
        if ($model->admission_type) {
            $hospitalization['admitSource'] = $this->createCodeableConcept(
                $model->admission_type,
                ucfirst($model->admission_type),
                $this->baseUrl . '/admission-source'
            );
        }

        // Add discharge disposition if discharged
        if ($model->status === 'discharged' && $model->discharge_date) {
            $hospitalization['dischargeDisposition'] = $this->createCodeableConcept(
                'home',
                'Discharged to Home',
                'http://terminology.hl7.org/CodeSystem/discharge-disposition'
            );
        }

        return $hospitalization;
    }

    /**
     * Validate Admission model
     */
    public function validate(Model $model): bool
    {
        if (!$model instanceof Admission) {
            return false;
        }

        // Basic validation - must have patient and admission date
        return !empty($model->patient_id) && !empty($model->admission_date);
    }
}