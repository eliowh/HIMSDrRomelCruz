<?php

namespace App\Services\FHIR\Resources;

use App\Models\Patient;
use App\Services\FHIR\AbstractFhirResource;
use Illuminate\Database\Eloquent\Model;

class FhirPatient extends AbstractFhirResource
{
    /**
     * Get the FHIR resource type
     */
    public function getResourceType(): string
    {
        return 'Patient';
    }

    /**
     * Transform Patient model to FHIR Patient resource
     */
    public function transform(Model $model): array
    {
        if (!$model instanceof Patient) {
            throw new \InvalidArgumentException('Model must be an instance of Patient');
        }

        if (!$this->validate($model)) {
            throw new \InvalidArgumentException('Invalid patient model');
        }

        $resource = $this->createBaseResource($model);

        // Add identifiers
        $resource['identifier'] = $this->buildIdentifiers($model);

        // Add active status
        $resource['active'] = $model->status !== 'discharged';

        // Add name
        $resource['name'] = [$this->createHumanName(
            $model->last_name ?? '',
            $model->first_name ?? '',
            $model->middle_name
        )];

        // Add gender
        if ($model->sex) {
            $resource['gender'] = strtolower($model->sex) === 'male' ? 'male' : 'female';
        }

        // Add birth date
        if ($model->date_of_birth) {
            $resource['birthDate'] = $this->formatFhirDate($model->date_of_birth);
        }

        // Add contact information
        if ($model->contact_number) {
            $resource['telecom'] = [
                $this->createContactPoint($model->contact_number)
            ];
        }

        // Add address
        if ($model->city || $model->province || $model->barangay) {
            $resource['address'] = [
                $this->createAddress(
                    $model->city,
                    $model->province,
                    $model->nationality === 'Filipino' ? 'Philippines' : null,
                    $model->barangay
                )
            ];
        }

        // Add extensions for additional data
        $resource['extension'] = $this->buildExtensions($model);

        // Add patient number as additional identifier
        if ($model->patient_no) {
            $resource['identifier'][] = $this->createIdentifier(
                (string) $model->patient_no,
                $this->baseUrl . '/patient-number',
                'Patient Number'
            );
        }

        return $resource;
    }

    /**
     * Build patient identifiers
     */
    private function buildIdentifiers(Patient $model): array
    {
        $identifiers = [];

        // Medical Record Number (MRN) - using the ID
        $identifiers[] = $this->createIdentifier(
            (string) $model->id,
            'http://terminology.hl7.org/CodeSystem/v2-0203',
            'MR'
        );

        // Patient ID using the formatted patient ID
        if ($model->patient_id) {
            $identifiers[] = $this->createIdentifier(
                $model->patient_id,
                $this->baseUrl . '/patient-id',
                'Patient ID'
            );
        }

        return $identifiers;
    }

    /**
     * Build extensions for additional patient data
     */
    private function buildExtensions(Patient $model): array
    {
        $extensions = [];

        // Age information
        if ($model->age_years !== null || $model->age_months !== null || $model->age_days !== null) {
            $extensions[] = [
                'url' => $this->baseUrl . '/StructureDefinition/patient-age',
                'extension' => [
                    [
                        'url' => 'years',
                        'valueInteger' => $model->age_years ?? 0
                    ],
                    [
                        'url' => 'months',
                        'valueInteger' => $model->age_months ?? 0
                    ],
                    [
                        'url' => 'days',
                        'valueInteger' => $model->age_days ?? 0
                    ]
                ]
            ];
        }

        // Nationality
        if ($model->nationality) {
            $extensions[] = [
                'url' => 'http://hl7.org/fhir/StructureDefinition/patient-nationality',
                'extension' => [
                    [
                        'url' => 'code',
                        'valueCodeableConcept' => $this->createCodeableConcept(
                            $model->nationality === 'Filipino' ? 'PH' : 'UNK',
                            $model->nationality,
                            'urn:iso:std:iso:3166'
                        )
                    ]
                ]
            ];
        }

        // Current admission status
        $currentAdmission = $model->currentAdmission;
        if ($currentAdmission) {
            $extensions[] = [
                'url' => $this->baseUrl . '/StructureDefinition/current-admission',
                'extension' => [
                    [
                        'url' => 'admissionId',
                        'valueString' => (string) $currentAdmission->id
                    ],
                    [
                        'url' => 'admissionNumber',
                        'valueString' => $currentAdmission->admission_number
                    ],
                    [
                        'url' => 'status',
                        'valueString' => $currentAdmission->status
                    ]
                ]
            ];
        }

        return $extensions;
    }

    /**
     * Validate Patient model
     */
    public function validate(Model $model): bool
    {
        if (!$model instanceof Patient) {
            return false;
        }

        // Basic validation - patient must have at least first and last name
        return !empty($model->first_name) && !empty($model->last_name);
    }
}