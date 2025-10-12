<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DoctorAccountSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all doctors from the doctorslist table
        $doctors = DB::table('doctorslist')->get();

        foreach ($doctors as $doctor) {
            // Get the full doctor name from the first column
            $fullName = trim($doctor->name ?? $doctor->doctor_name ?? collect($doctor)->first());
            
            if (empty($fullName)) {
                continue; // Skip if no name found
            }

            // Extract first name for email (remove titles like Dr., MD, etc.)
            $firstName = $this->extractFirstName($fullName);
            
            // Create email from first name
            $email = strtolower($firstName) . '@gmail.com';
            
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $this->command->info("Doctor account already exists for: {$fullName} ({$email})");
                continue;
            }

            // Create the doctor account
            User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('Doctor123!'),
                'role' => 'doctor',
                'email_verified_at' => now(),
            ]);

            $this->command->info("Created doctor account: {$fullName} ({$email})");
        }

        $this->command->info("Doctor account seeding completed!");
    }

    /**
     * Extract first name from full name, removing titles
     */
    private function extractFirstName($fullName)
    {
        // Remove common titles
        $name = preg_replace('/^(Dr\.?|Doctor|MD|M\.D\.)\s+/i', '', $fullName);
        
        // Split by space and get first word
        $nameParts = explode(' ', trim($name));
        $firstName = $nameParts[0];
        
        // Remove any non-alphabetic characters and ensure it's valid for email
        $firstName = preg_replace('/[^a-zA-Z]/', '', $firstName);
        
        return $firstName ?: 'doctor';
    }
}