<?php

namespace App\Http\Requests\FHIR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\FhirException;
use Illuminate\Http\Exceptions\HttpResponseException;

class FhirSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // FHIR endpoints are publicly accessible for now
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'patient_no' => 'sometimes|string|max:50',
            'birthdate' => 'sometimes|date_format:Y-m-d',
            'gender' => 'sometimes|in:male,female,other,unknown',
            '_count' => 'sometimes|integer|min:1|max:100',
            '_offset' => 'sometimes|integer|min:0',
            '_sort' => 'sometimes|string|max:100',
            '_include' => 'sometimes|string|max:200',
            '_revinclude' => 'sometimes|string|max:200'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'The name parameter must not exceed 255 characters',
            'patient_no.max' => 'The patient number must not exceed 50 characters',
            'birthdate.date_format' => 'The birthdate must be in YYYY-MM-DD format',
            'gender.in' => 'The gender must be one of: male, female, other, unknown',
            '_count.integer' => 'The _count parameter must be an integer',
            '_count.min' => 'The _count parameter must be at least 1',
            '_count.max' => 'The _count parameter must not exceed 100',
            '_offset.integer' => 'The _offset parameter must be an integer',
            '_offset.min' => 'The _offset parameter must be 0 or greater'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        
        throw new HttpResponseException(
            FhirException::validation(
                'Invalid search parameters',
                $errors
            )->toFhirResponse()
        );
    }
}