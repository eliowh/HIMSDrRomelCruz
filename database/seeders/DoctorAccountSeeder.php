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

            // Generate a unique email. If 'maria' already exists, append an initial from other names.
            $email = $this->generateUniqueEmail($fullName, $firstName);

            // Create the doctor account
            User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('Doctor123!'),
                'role' => 'doctor',
                'title' => 'MD',
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

    /**
     * Generate a unique email based on first name. If base already exists,
     * append the first letter of the next name part(s). Falls back to numeric
     * suffixes if needed.
     */
    private function generateUniqueEmail($fullName, $firstName)
    {
        $base = strtolower(preg_replace('/[^a-zA-Z]/', '', $firstName));
        $local = $base;

        // Prepare name parts without titles
        $name = preg_replace('/^(Dr\.?|Doctor|MD|M\.D\.)\s+/i', '', $fullName);
        $parts = preg_split('/\s+/', trim($name));
        $parts = array_values(array_filter(array_map(function ($p) {
            return preg_replace('/[^a-zA-Z]/', '', $p);
        }, $parts)));

        // remove the first part (already used)
        if (count($parts) > 0) {
            array_shift($parts);
        }

        // Try appending initials from other name parts
        foreach ($parts as $part) {
            if ($part === '') continue;
            $candidate = $local . strtolower(substr($part, 0, 1)) . '@gmail.com';
            if (!User::where('email', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Fallback: try numeric suffixes
        $i = 1;
        while (User::where('email', $local . $i . '@gmail.com')->exists()) {
            $i++;
            // safety cap to avoid infinite loop
            if ($i > 1000) break;
        }

        return $local . $i . '@gmail.com';
    }
}