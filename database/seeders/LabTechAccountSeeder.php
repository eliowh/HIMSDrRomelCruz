<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabTechAccountSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all lab technicians from the labtechlist table
        $labTechs = DB::table('labtechlist')->get();

        foreach ($labTechs as $labTech) {
            // Get the technician data (COL 1 = name, COL 2 = license number)
            $columns = collect($labTech)->values();
            
            if ($columns->count() < 2) {
                $this->command->warn("Skipping lab tech record - insufficient data: " . json_encode($labTech));
                continue;
            }

            $fullName = trim($columns[0]); // First column is the name
            $licenseNumber = trim($columns[1]); // Second column is the license number
            
            if (empty($fullName)) {
                $this->command->warn("Skipping lab tech record - empty name");
                continue;
            }

            // Extract first name for email
            $firstName = $this->extractFirstName($fullName);
            
            // Create email from first name
            $email = strtolower($firstName) . '@gmail.com';
            
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $this->command->info("Lab Tech account already exists for: {$fullName} ({$email})");
                continue;
            }

            // Create the lab technician account with license number
            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('Labtech123!'),
                'role' => 'lab_technician',
                'license_number' => $licenseNumber,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info("Created lab tech account: {$fullName} ({$email}) - License: {$licenseNumber}");
        }

        $this->command->info("Lab technician account seeding completed!");
        $this->command->info("Note: License numbers are preserved for lab result PDFs");
    }

    /**
     * Extract first name from full name, removing titles
     */
    private function extractFirstName($fullName)
    {
        // Remove common titles for lab techs
        $name = preg_replace('/^(Mr\.?|Ms\.?|Mrs\.?|MT|RMT|MLT)\s+/i', '', $fullName);
        
        // Split by space and get first word
        $nameParts = explode(' ', trim($name));
        $firstName = $nameParts[0];
        
        // Remove any non-alphabetic characters and ensure it's valid for email
        $firstName = preg_replace('/[^a-zA-Z]/', '', $firstName);
        
        return $firstName ?: 'labtech';
    }
}