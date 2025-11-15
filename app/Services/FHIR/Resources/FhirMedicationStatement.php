<?php

namespace App\Services\FHIR\Resources;

use App\Models\PatientMedicine;
use App\Models\PharmacyRequest;
use App\Services\FHIR\AbstractFhirResource;
use Illuminate\Database\Eloquent\Model;

class FhirMedicationStatement extends AbstractFhirResource
{
    /**
     * Get the FHIR resource type
     */
    public function getResourceType(): string
    {
        return 'MedicationStatement';
    }

    /**
     * Transform PatientMedicine or PharmacyRequest model to FHIR MedicationStatement resource
     */
    public function transform(Model $model): array
    {
        if (!$model instanceof PatientMedicine && !$model instanceof PharmacyRequest) {
            throw new \InvalidArgumentException('Model must be an instance of PatientMedicine or PharmacyRequest');
        }

        if (!$this->validate($model)) {
            throw new \InvalidArgumentException('Invalid medication model');
        }

        $resource = $this->createBaseResource($model);

        // Add identifier
        $resource['identifier'] = [
            $this->createIdentifier(
                ($model instanceof PatientMedicine ? 'PM' : 'PR') . '-' . $model->id,
                $this->baseUrl . '/medication-statement-id',
                'Medication Statement ID'
            )
        ];

        // Add status based on model type and status
        $resource['status'] = $this->mapStatus($model);

        // Add medication reference/coding
        $resource['medicationCodeableConcept'] = $this->buildMedicationCode($model);

        // Add subject (patient reference)
        if ($model->patient_id) {
            $resource['subject'] = $this->createReference(
                "Patient/{$model->patient_id}",
                $model->patient_name ?? ($model->patient ? $model->patient->display_name : null)
            );
        }

        // Add context (encounter) if available through patient's current admission
        if ($model->patient && $model->patient->currentAdmission) {
            $resource['context'] = $this->createReference(
                "Encounter/{$model->patient->currentAdmission->id}"
            );
        }

        // Add effective datetime
        $resource['effectiveDateTime'] = $this->getEffectiveDateTime($model);

        // Add date asserted (when the statement was recorded)
        $resource['dateAsserted'] = $this->formatFhirDateTime($model->created_at);

        // Add information source (who dispensed or prescribed)
        if ($this->getDispensedBy($model)) {
            $resource['informationSource'] = [
                'display' => $this->getDispensedBy($model)
            ];
        }

        // Add dosage information
        if ($model->quantity) {
            $resource['dosage'] = $this->buildDosage($model);
        }

        // Add notes
        if ($model->notes) {
            $resource['note'] = [
                [
                    'text' => $model->notes
                ]
            ];
        }

        return $resource;
    }

    /**
     * Map model status to FHIR medication statement status
     */
    private function mapStatus(Model $model): string
    {
        if ($model instanceof PatientMedicine) {
            // PatientMedicine represents completed/dispensed medications
            return 'active';
        }

        if ($model instanceof PharmacyRequest) {
            $statusMap = [
                'pending' => 'intended',
                'approved' => 'intended', 
                'dispensed' => 'active',
                'cancelled' => 'stopped',
                'rejected' => 'stopped'
            ];

            return $statusMap[$model->status] ?? 'unknown';
        }

        return 'unknown';
    }

    /**
     * Build medication code from model
     */
    private function buildMedicationCode(Model $model): array
    {
        $medicationName = $this->getMedicationName($model);
        $genericName = $model->generic_name ?? '';
        $brandName = $model->brand_name ?? '';

        $coding = [];

        // Add generic name coding if available
        if ($genericName) {
            $coding[] = [
                'system' => 'http://www.nlm.nih.gov/research/umls/rxnorm',
                'code' => $this->sanitizeCodeValue($genericName),
                'display' => $genericName
            ];
        }

        // Add brand name coding if available and different from generic
        if ($brandName && $brandName !== $genericName) {
            $coding[] = [
                'system' => $this->baseUrl . '/medication-brand-names',
                'code' => $this->sanitizeCodeValue($brandName),
                'display' => $brandName
            ];
        }

        $concept = [
            'text' => $medicationName
        ];

        if (!empty($coding)) {
            $concept['coding'] = $coding;
        }

        return $concept;
    }

    /**
     * Get medication name from model
     */
    private function getMedicationName(Model $model): string
    {
        // Prefer brand name, fall back to generic name
        return $model->brand_name ?: $model->generic_name ?: 'Unknown Medication';
    }

    /**
     * Get effective datetime from model
     */
    private function getEffectiveDateTime(Model $model): string
    {
        if ($model instanceof PatientMedicine && $model->dispensed_at) {
            return $this->formatFhirDateTime($model->dispensed_at);
        }

        if ($model instanceof PharmacyRequest && $model->requested_at) {
            return $this->formatFhirDateTime($model->requested_at);
        }

        return $this->formatFhirDateTime($model->created_at);
    }

    /**
     * Get who dispensed the medication
     */
    private function getDispensedBy(Model $model): ?string
    {
        if ($model instanceof PatientMedicine) {
            return $model->dispensed_by;
        }

        if ($model instanceof PharmacyRequest) {
            return $model->requested_by_name ?? null;
        }

        return null;
    }

    /**
     * Build dosage information
     */
    private function buildDosage(Model $model): array
    {
        $dosage = [
            'sequence' => 1
        ];

        // Add text instruction if notes available
        if ($model->notes) {
            $dosage['text'] = $model->notes;
        }

        // Add dose quantity
        if ($model->quantity) {
            $dosage['doseAndRate'] = [
                [
                    'doseQuantity' => [
                        'value' => (float) $model->quantity,
                        'unit' => $this->getQuantityUnit($model),
                        'system' => 'http://unitsofmeasure.org'
                    ]
                ]
            ];
        }

        return [$dosage];
    }

    /**
     * Get quantity unit (attempt to infer from medication name or default)
     */
    private function getQuantityUnit(Model $model): string
    {
        $medicationName = strtolower($this->getMedicationName($model));
        
        // Common medication unit patterns
        if (strpos($medicationName, 'tablet') !== false || strpos($medicationName, 'tab') !== false) {
            return 'tablet';
        } elseif (strpos($medicationName, 'capsule') !== false || strpos($medicationName, 'cap') !== false) {
            return 'capsule';
        } elseif (strpos($medicationName, 'ml') !== false || strpos($medicationName, 'liquid') !== false) {
            return 'mL';
        } elseif (strpos($medicationName, 'mg') !== false) {
            return 'mg';
        }
        
        // Default to generic unit
        return 'unit';
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
     * Validate model
     */
    public function validate(Model $model): bool
    {
        if (!$model instanceof PatientMedicine && !$model instanceof PharmacyRequest) {
            return false;
        }

        // Basic validation - must have patient and medication name
        $hasPatient = !empty($model->patient_id);
        $hasMedication = !empty($model->generic_name) || !empty($model->brand_name);

        return $hasPatient && $hasMedication;
    }
}