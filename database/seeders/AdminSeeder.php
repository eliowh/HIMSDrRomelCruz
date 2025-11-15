<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@hims.com'],
            [
                'name' => 'HimsAdmin',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}