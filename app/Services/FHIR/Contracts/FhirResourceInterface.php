<?php

namespace App\Services\FHIR\Contracts;

use Illuminate\Database\Eloquent\Model;

interface FhirResourceInterface
{
    /**
     * Transform the given model to FHIR resource format
     */
    public function transform(Model $model): array;

    /**
     * Get the FHIR resource type
     */
    public function getResourceType(): string;

    /**
     * Validate the model before transformation
     */
    public function validate(Model $model): bool;

    /**
     * Get the FHIR version being used
     */
    public function getFhirVersion(): string;
}