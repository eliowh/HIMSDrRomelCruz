<?php

namespace App\Services\FHIR\Resources;

use App\Models\LabOrder;
use App\Models\LabAnalysis;
use App\Services\FHIR\AbstractFhirResource;
use Illuminate\Database\Eloquent\Model;

class FhirDiagnosticReport extends AbstractFhirResource
{
    public function getResourceType(): string
    {
        return 'DiagnosticReport';
    }

    /**
     * Transform LabOrder/LabAnalysis relationship into DiagnosticReport
     */
    public function transform(Model $model): array
    {
        if ($model instanceof LabAnalysis) {
            $analysis = $model;
            $labOrder = $analysis->labOrder;
        } elseif ($model instanceof LabOrder) {
            $labOrder = $model;
            // pick first analysis if present
            $analysis = $labOrder->analyses()->latest()->first();
        } else {
            throw new \InvalidArgumentException('Model must be an instance of LabOrder or LabAnalysis');
        }

        if (!$labOrder) {
            throw new \InvalidArgumentException('Lab order not found for DiagnosticReport');
        }

        $resource = $this->createBaseResource($labOrder);

        // identifier
        $resource['identifier'] = [
            $this->createIdentifier((string) $labOrder->id, $this->baseUrl . '/lab-order-id', 'Lab Order ID')
        ];

        // status
        $resource['status'] = $this->mapStatus($labOrder->status ?? $analysis->status ?? 'unknown');

        // category - laboratory
        $resource['category'] = [
            $this->createCodeableConcept('laboratory', 'Laboratory', 'http://terminology.hl7.org/CodeSystem/diagnosticservice')
        ];

        // code - the type of report (use test_requested)
        $resource['code'] = $this->createCodeableConcept(
            $this->sanitizeCodeValue($labOrder->test_requested ?? 'LAB'),
            $labOrder->test_requested ?? 'Lab Test',
            $this->baseUrl . '/lab-test-codes'
        );

        // subject
        if ($labOrder->patient_id) {
            $resource['subject'] = $this->createReference("Patient/{$labOrder->patient_id}", $labOrder->patient_name);
        }

        // context - encounter if present
        if ($labOrder->admission_id) {
            $resource['context'] = $this->createReference("Encounter/{$labOrder->admission_id}");
        }

        // effective and issued
        if ($labOrder->requested_at) {
            $resource['effectiveDateTime'] = $this->formatFhirDateTime($labOrder->requested_at);
        }

        if ($labOrder->completed_at) {
            $resource['issued'] = $this->formatFhirDateTime($labOrder->completed_at);
        } elseif ($analysis && $analysis->analyzed_at) {
            $resource['issued'] = $this->formatFhirDateTime($analysis->analyzed_at);
        }

        // performer - laboratory
        if ($analysis && $analysis->doctor_id) {
            $resource['performer'] = [
                $this->createReference("Practitioner/{$analysis->doctor_id}")
            ];
        } elseif ($labOrder->lab_tech_id) {
            $resource['performer'] = [
                $this->createReference("Practitioner/{$labOrder->lab_tech_id}")
            ];
        }

        // results - link Observations (in this implementation we have simple results in LabOrder)
        $resultEntries = [];

        if (!empty($labOrder->results)) {
            // put results into a contained Observation-like structure
            $resultEntries[] = [
                'resource' => [
                    'resourceType' => 'Observation',
                    'id' => 'obs-' . $labOrder->id,
                    'status' => ($labOrder->status === 'completed') ? 'final' : 'preliminary',
                    'code' => $this->createCodeableConcept($this->sanitizeCodeValue($labOrder->test_requested ?? 'LAB'), $labOrder->test_requested ?? 'Lab Test', $this->baseUrl . '/lab-test-codes'),
                    'subject' => $resource['subject'] ?? null,
                    'effectiveDateTime' => $resource['effectiveDateTime'] ?? null,
                    'valueString' => $labOrder->results
                ]
            ];
        }

        if (!empty($resultEntries)) {
            $resource['result'] = array_map(function ($entry) {
                return $entry['resource'];
            }, $resultEntries);
        }

        // Add conclusion from analysis notes/recommendations
        if ($analysis && ($analysis->clinical_notes || $analysis->recommendations)) {
            $conclusion = trim(trim((string)$analysis->clinical_notes) . " " . trim((string)$analysis->recommendations));
            if ($conclusion) {
                $resource['conclusion'] = $conclusion;
            }
        } elseif (!empty($labOrder->notes)) {
            $resource['conclusion'] = $labOrder->notes;
        }

        return $resource;
    }

    private function mapStatus(string $status): string
    {
        $map = [
            'requested' => 'preliminary',
            'in_progress' => 'in-progress',
            'completed' => 'final',
            'cancelled' => 'amended'
        ];

        return $map[$status] ?? 'unknown';
    }
}
