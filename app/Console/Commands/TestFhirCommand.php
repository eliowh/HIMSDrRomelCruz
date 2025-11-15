<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FHIR\FhirService;
use App\Models\Patient;

class TestFhirCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fhir:test {--patient=1 : Patient ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the FHIR layer functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Testing FHIR Layer...');
        $this->newLine();

        $fhirService = new FhirService();
        $patientId = $this->option('patient');

        // Test 1: Service initialization
        $this->info('1. Testing FHIR Service initialization...');
        try {
            $capability = $fhirService->getCapabilityStatement();
            $this->info("   ✅ FHIR Service initialized (Version: {$capability['fhirVersion']})");
        } catch (\Exception $e) {
            $this->error("   ❌ Failed: " . $e->getMessage());
            return 1;
        }

        // Test 2: Patient transformation
        $this->info('2. Testing Patient transformation...');
        try {
            $patient = Patient::with(['admissions', 'labOrders', 'medicines'])->find($patientId);
            
            if (!$patient) {
                $this->warn("   ⚠ Patient ID {$patientId} not found, using first available patient");
                $patient = Patient::with(['admissions', 'labOrders', 'medicines'])->first();
                
                if (!$patient) {
                    $this->error("   ❌ No patients found in database");
                    return 1;
                }
            }

            $fhirPatient = $fhirService->transformToFhir($patient);
            $name = $fhirPatient['name'][0]['text'] ?? 'Unknown';
            $this->info("   ✅ Patient transformed: {$name} (ID: {$patient->id})");
            
            // Test 3: Patient bundle
            $this->info('3. Testing Patient bundle...');
            $bundle = $fhirService->getPatientBundle($patient->id);
            $this->info("   ✅ Bundle created with {$bundle['total']} entries");
            
            // List bundle entries
            foreach ($bundle['entry'] as $index => $entry) {
                $resourceType = $entry['resource']['resourceType'];
                $resourceId = $entry['resource']['id'];
                $this->line("      - {$resourceType}/{$resourceId}");
            }

            // Test 4: Search functionality
            $this->info('4. Testing search functionality...');
            $searchResults = $fhirService->searchPatients(['name' => substr($patient->first_name, 0, 3), '_count' => 5]);
            $this->info("   ✅ Search completed, found {$searchResults['total']} results");

            // Test 5: Validation
            $this->info('5. Testing validation...');
            $errors = $fhirService->validateResource($fhirPatient);
            if (empty($errors)) {
                $this->info('   ✅ Patient resource validation passed');
            } else {
                $this->warn('   ⚠ Validation issues: ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error during testing: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('🎉 All tests completed successfully!');
        
        // Show available endpoints
        $baseUrl = config('app.url');
        $this->newLine();
        $this->info('Available FHIR endpoints:');
        $this->line("GET  {$baseUrl}/api/fhir/metadata");
        $this->line("GET  {$baseUrl}/api/fhir/Patient");
        $this->line("GET  {$baseUrl}/api/fhir/Patient/{$patient->id}");
        $this->line("GET  {$baseUrl}/api/fhir/Patient/{$patient->id}/\$everything");
        
        if ($patient->admissions->first()) {
            $admissionId = $patient->admissions->first()->id;
            $this->line("GET  {$baseUrl}/api/fhir/Encounter/{$admissionId}");
        }
        
        if ($patient->labOrders->first()) {
            $labId = $patient->labOrders->first()->id;
            $this->line("GET  {$baseUrl}/api/fhir/Observation/{$labId}");
        }

        $this->newLine();
        $this->info('💡 Try the browser-based tester: ' . $baseUrl . '/fhir-tester.html');

        return 0;
    }
}
