<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirException extends Exception
{
    private string $fhirCode;
    private string $severity;
    private array $details;

    public function __construct(
        string $message, 
        string $fhirCode = 'exception', 
        string $severity = 'error',
        array $details = [],
        int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $httpCode, $previous);
        $this->fhirCode = $fhirCode;
        $this->severity = $severity;
        $this->details = $details;
    }

    /**
     * Create a FHIR-compliant error response
     */
    public function toFhirResponse(): JsonResponse
    {
        $operationOutcome = [
            'resourceType' => 'OperationOutcome',
            'id' => uniqid('error-'),
            'meta' => [
                'lastUpdated' => now()->toISOString()
            ],
            'issue' => [
                [
                    'severity' => $this->severity,
                    'code' => $this->fhirCode,
                    'details' => [
                        'text' => $this->getMessage()
                    ]
                ]
            ]
        ];

        if (!empty($this->details)) {
            $operationOutcome['issue'][0]['diagnostics'] = implode('; ', $this->details);
        }

        return response()->json($operationOutcome, $this->getCode(), [
            'Content-Type' => 'application/fhir+json'
        ]);
    }

    public function getFhirCode(): string
    {
        return $this->fhirCode;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Create a not found exception
     */
    public static function notFound(string $resourceType, string $id): self
    {
        return new self(
            "The {$resourceType} resource with ID '{$id}' was not found",
            'not-found',
            'error',
            [],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Create a validation exception
     */
    public static function validation(string $message, array $details = []): self
    {
        return new self(
            $message,
            'structure',
            'error', 
            $details,
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Create an invalid format exception
     */
    public static function invalidFormat(string $message): self
    {
        return new self(
            $message,
            'invalid',
            'error',
            [],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Create a processing exception
     */
    public static function processing(string $message, \Throwable $previous = null): self
    {
        return new self(
            $message,
            'processing',
            'error',
            [],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $previous
        );
    }

    /**
     * Create an authentication exception
     */
    public static function authentication(string $message = 'Authentication required'): self
    {
        return new self(
            $message,
            'security',
            'error',
            [],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Create an authorization exception
     */
    public static function authorization(string $message = 'Insufficient permissions'): self
    {
        return new self(
            $message,
            'forbidden',
            'error',
            [],
            Response::HTTP_FORBIDDEN
        );
    }
}