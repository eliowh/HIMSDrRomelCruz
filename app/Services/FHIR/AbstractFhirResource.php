<?php

namespace App\Services\FHIR;

use App\Services\FHIR\Contracts\FhirResourceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

abstract class AbstractFhirResource implements FhirResourceInterface
{
    protected string $fhirVersion = '4.0.1';
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/api/fhir';
    }

    /**
     * Get the FHIR version being used
     */
    public function getFhirVersion(): string
    {
        return $this->fhirVersion;
    }

    /**
     * Validate the model before transformation
     */
    public function validate(Model $model): bool
    {
        return $model->exists;
    }

    /**
     * Generate a FHIR-compliant resource ID
     */
    protected function generateResourceId(Model $model): string
    {
        return $this->getResourceType() . '/' . $model->id;
    }

    /**
     * Generate a full URL for the resource
     */
    protected function generateResourceUrl(Model $model): string
    {
        return $this->baseUrl . '/' . $this->generateResourceId($model);
    }

    /**
     * Create base FHIR resource structure
     */
    protected function createBaseResource(Model $model): array
    {
        return [
            'resourceType' => $this->getResourceType(),
            'id' => (string) $model->id,
            'meta' => [
                'versionId' => '1',
                'lastUpdated' => $this->formatFhirDateTime($model->updated_at ?? now()),
                'profile' => [
                    "http://hl7.org/fhir/StructureDefinition/{$this->getResourceType()}"
                ]
            ]
        ];
    }

    /**
     * Format datetime for FHIR compliance (ISO 8601)
     */
    protected function formatFhirDateTime($datetime): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }
        
        return $datetime ? $datetime->toISOString() : now()->toISOString();
    }

    /**
     * Format date for FHIR compliance (YYYY-MM-DD)
     */
    protected function formatFhirDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format('Y-m-d');
    }

    /**
     * Create FHIR identifier structure
     */
    protected function createIdentifier(string $value, string $system = null, string $type = null): array
    {
        $identifier = [
            'value' => $value
        ];

        if ($system) {
            $identifier['system'] = $system;
        }

        if ($type) {
            $identifier['type'] = [
                'text' => $type
            ];
        }

        return $identifier;
    }

    /**
     * Create FHIR reference structure
     */
    protected function createReference(string $reference, string $display = null): array
    {
        $ref = [
            'reference' => $reference
        ];

        if ($display) {
            $ref['display'] = $display;
        }

        return $ref;
    }

    /**
     * Create FHIR CodeableConcept structure
     */
    protected function createCodeableConcept(string $code, string $display, string $system = null): array
    {
        $concept = [
            'text' => $display
        ];

        if ($code && $system) {
            $concept['coding'] = [
                [
                    'system' => $system,
                    'code' => $code,
                    'display' => $display
                ]
            ];
        }

        return $concept;
    }

    /**
     * Create FHIR HumanName structure
     */
    protected function createHumanName(string $family, string $given, string $middle = null, string $use = 'official'): array
    {
        $name = [
            'use' => $use,
            'family' => $family,
            'given' => [$given]
        ];

        if ($middle) {
            $name['given'][] = $middle;
        }

        $name['text'] = trim(($given ?? '') . ' ' . ($middle ?? '') . ' ' . ($family ?? ''));

        return $name;
    }

    /**
     * Create FHIR Address structure
     */
    protected function createAddress(string $city = null, string $state = null, string $country = null, string $district = null): array
    {
        $address = [
            'use' => 'home',
            'type' => 'physical'
        ];

        $line = [];
        if ($district) {
            $line[] = $district;
        }

        if (!empty($line)) {
            $address['line'] = $line;
        }

        if ($city) {
            $address['city'] = $city;
        }

        if ($state) {
            $address['state'] = $state;
        }

        if ($country) {
            $address['country'] = $country;
        }

        // Create text representation
        $textParts = array_filter([$district, $city, $state, $country]);
        if (!empty($textParts)) {
            $address['text'] = implode(', ', $textParts);
        }

        return $address;
    }

    /**
     * Create FHIR ContactPoint structure
     */
    protected function createContactPoint(string $value, string $system = 'phone', string $use = 'mobile'): array
    {
        return [
            'system' => $system,
            'value' => $value,
            'use' => $use
        ];
    }
}