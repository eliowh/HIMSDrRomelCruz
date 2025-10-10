<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class TestDoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test doctor
        User::create([
            'name' => 'Dr. John Smith',
            'email' => 'dr.johnsmith@hospital.com',
            'password' => bcrypt('password123'),
            'role' => 'doctor',
        ]);

        echo "Test doctor created successfully!\n";
        echo "Email: dr.johnsmith@hospital.com\n";
        echo "Password: password123\n";
    }
}
