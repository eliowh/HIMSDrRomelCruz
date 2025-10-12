# FHIR Layer Implementation for HIMS Dr. Romel Cruz

## Overview

This implementation provides a comprehensive FHIR (Fast Healthcare Interoperability Resources) R4 compliant layer for the Hospital Information Management System. It converts MySQL data from your Laravel application into standardized FHIR JSON format, enabling healthcare data interoperability.

## Architecture

### Core Components

1. **Abstract Base Classes & Interfaces**
   - `FhirResourceInterface` - Defines contract for FHIR transformers
   - `AbstractFhirResource` - Provides common functionality for all FHIR resources

2. **FHIR Resource Transformers**
   - `FhirPatient` - Converts Patient model to FHIR Patient resource
   - `FhirEncounter` - Converts Admission model to FHIR Encounter resource
   - `FhirObservation` - Converts LabOrder model to FHIR Observation resource
   - `FhirMedicationStatement` - Converts PatientMedicine/PharmacyRequest to FHIR MedicationStatement resource

3. **Service Layer**
   - `FhirService` - Orchestrates transformations and provides unified API

4. **API Layer**
   - `FhirController` - RESTful FHIR-compliant API endpoints
   - `FhirMiddleware` - Handles FHIR-specific headers and responses
   - `FhirException` - FHIR-compliant error handling

## Supported FHIR Resources

### 1. Patient Resource
Maps your `Patient` model to FHIR Patient resource including:
- Identifiers (MRN, Patient Number)
- Demographics (name, gender, birth date)
- Contact information (phone, address)
- Extensions for age breakdown and nationality

### 2. Encounter Resource
Maps your `Admission` model to FHIR Encounter resource including:
- Admission details and status
- Encounter class and type
- Patient reference
- Doctor/practitioner information
- Admission and discharge dates
- Diagnosis information

### 3. Observation Resource
Maps your `LabOrder` model to FHIR Observation resource including:
- Lab test details and status
- Patient and encounter references
- Test results and interpretations
- Lab technician information
- Timing information

### 4. MedicationStatement Resource
Maps your `PatientMedicine` and `PharmacyRequest` models to FHIR MedicationStatement including:
- Medication details (generic/brand names)
- Patient references
- Dosage information
- Dispensing information

## API Endpoints

### Base URL: `/api/fhir`

### Metadata
- `GET /metadata` - FHIR Capability Statement

### Patient Resources
- `GET /Patient` - Search patients with query parameters
- `GET /Patient/{id}` - Get specific patient
- `GET /Patient/{id}/$everything` - Get patient with all related resources

### Encounter Resources
- `GET /Encounter/{id}` - Get specific encounter (admission)

### Observation Resources
- `GET /Observation/{id}` - Get specific observation (lab order)

### MedicationStatement Resources
- `GET /MedicationStatement/{type}-{id}` - Get medication statement
  - `pm-{id}` for PatientMedicine records
  - `pr-{id}` for PharmacyRequest records

### Operations
- `POST /$validate` - Validate FHIR resource

## Search Parameters

### Patient Search
- `name` - Search by patient name (partial matching)
- `patient_no` - Search by patient number
- `birthdate` - Search by birth date (YYYY-MM-DD format)
- `gender` - Filter by gender (male, female, other, unknown)
- `_count` - Number of results to return (1-100, default: 20)
- `_offset` - Pagination offset (default: 0)

### Examples:
```
GET /api/fhir/Patient?name=Juan
GET /api/fhir/Patient?gender=male&_count=10
GET /api/fhir/Patient?birthdate=1985-06-15
```

## Usage Examples

### 1. Using the FHIR Service Directly

```php
use App\Services\FHIR\FhirService;
use App\Models\Patient;

$fhirService = new FhirService();

// Transform single patient
$patient = Patient::find(1);
$fhirPatient = $fhirService->transformToFhir($patient);

// Get patient bundle with all related resources
$bundle = $fhirService->getPatientBundle(1);

// Search patients
$searchResults = $fhirService->searchPatients([
    'name' => 'Juan',
    '_count' => 10
]);
```

### 2. API Usage (HTTP Requests)

```bash
# Get capability statement
curl -H "Accept: application/fhir+json" \
     http://your-app.com/api/fhir/metadata

# Get specific patient
curl -H "Accept: application/fhir+json" \
     http://your-app.com/api/fhir/Patient/1

# Search patients
curl -H "Accept: application/fhir+json" \
     "http://your-app.com/api/fhir/Patient?name=Juan&_count=5"

# Get patient with all related data
curl -H "Accept: application/fhir+json" \
     http://your-app.com/api/fhir/Patient/1/\$everything
```

### 3. Sample FHIR Patient Resource Output

```json
{
  "resourceType": "Patient",
  "id": "1",
  "meta": {
    "versionId": "1",
    "lastUpdated": "2025-10-12T10:30:00Z",
    "profile": ["http://hl7.org/fhir/StructureDefinition/Patient"]
  },
  "identifier": [
    {
      "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
      "value": "1",
      "type": { "text": "MR" }
    }
  ],
  "active": true,
  "name": [
    {
      "use": "official",
      "family": "Dela Cruz",
      "given": ["Juan", "Santos"],
      "text": "Juan Santos Dela Cruz"
    }
  ],
  "gender": "male",
  "birthDate": "1985-06-15",
  "telecom": [
    {
      "system": "phone",
      "value": "09171234567",
      "use": "mobile"
    }
  ],
  "address": [
    {
      "use": "home",
      "type": "physical",
      "line": ["Barangay Poblacion"],
      "city": "Malolos City",
      "state": "Bulacan",
      "country": "Philippines",
      "text": "Barangay Poblacion, Malolos City, Bulacan, Philippines"
    }
  ]
}
```

## Error Handling

The FHIR layer implements comprehensive error handling:

### HTTP Status Codes
- `200 OK` - Successful response
- `400 Bad Request` - Invalid request parameters
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `500 Internal Server Error` - Server errors

### FHIR OperationOutcome
All errors return FHIR-compliant OperationOutcome resources:

```json
{
  "resourceType": "OperationOutcome",
  "id": "error-123",
  "meta": {
    "lastUpdated": "2025-10-12T10:30:00Z"
  },
  "issue": [
    {
      "severity": "error",
      "code": "not-found",
      "details": {
        "text": "The Patient resource with ID '999' was not found"
      }
    }
  ]
}
```

## Testing

### Running the Test Script
```bash
php test_fhir_integration.php
```

This script will:
1. Test FHIR service initialization
2. Transform existing patient data
3. Create patient bundles
4. Test search functionality
5. Validate FHIR resources
6. Display available endpoints

### Manual Testing with cURL

```bash
# Test metadata endpoint
curl -X GET "http://localhost/api/fhir/metadata" \
     -H "Accept: application/fhir+json"

# Test patient search
curl -X GET "http://localhost/api/fhir/Patient" \
     -H "Accept: application/fhir+json"

# Test specific patient
curl -X GET "http://localhost/api/fhir/Patient/1" \
     -H "Accept: application/fhir+json"
```

## Configuration

### Environment Variables
No additional environment variables are required. The FHIR layer uses existing Laravel configuration.

### Customization
To customize the FHIR implementation:

1. **Extend Resource Transformers**: Inherit from `AbstractFhirResource`
2. **Add New Resource Types**: Create new transformer classes and register in `FhirService`
3. **Modify Search Parameters**: Update validation rules in `FhirSearchRequest`
4. **Custom Extensions**: Add hospital-specific extensions in transformer classes

## Security Considerations

### Current Implementation
- FHIR endpoints are publicly accessible (no authentication required)
- CORS enabled for cross-origin requests
- Input validation on all search parameters

### Production Recommendations
1. Implement authentication (OAuth2, API keys)
2. Add authorization based on roles/permissions
3. Enable HTTPS only
4. Implement rate limiting
5. Add audit logging
6. Consider data masking for sensitive information

## Performance Optimization

### Current Optimizations
- Eager loading of related models to reduce N+1 queries
- Pagination support for search results
- Caching-friendly headers

### Future Enhancements
1. Implement Redis caching for frequently accessed resources
2. Add database indexing on commonly searched fields
3. Implement bulk operations for large datasets
4. Add compression for large responses

## Compliance

This implementation follows:
- **FHIR R4 Specification** (v4.0.1)
- **HL7 FHIR Implementation Guide**
- **RESTful API Design Principles**
- **JSON Format Standards**

## Troubleshooting

### Common Issues

1. **"No FHIR transformer available"**
   - Ensure the model class is registered in `FhirService::initializeTransformers()`

2. **"Invalid patient model"**
   - Check that the model has required fields (first_name, last_name)

3. **"Resource validation failed"**
   - Review the validation errors in the response
   - Ensure all required FHIR fields are present

4. **404 errors on API routes**
   - Verify API routes are loaded in `bootstrap/app.php`
   - Clear route cache: `php artisan route:clear`

### Debug Mode
Enable debug logging in your `.env`:
```
LOG_LEVEL=debug
```

Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## Extensions and Future Development

### Planned Enhancements
1. Support for additional FHIR resources (Practitioner, Organization, Location)
2. FHIR Bulk Data export
3. Subscription support for real-time notifications
4. HL7 v2 message integration
5. Clinical decision support hooks

### Contributing
To extend the FHIR implementation:
1. Follow the existing code patterns
2. Implement proper validation
3. Add comprehensive tests
4. Update documentation
5. Follow FHIR specification guidelines

## Support
For questions or issues with the FHIR implementation, please refer to:
- FHIR R4 Documentation: https://hl7.org/fhir/R4/
- Laravel Documentation: https://laravel.com/docs
- This implementation's code comments and examples