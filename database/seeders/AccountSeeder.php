<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Dr. John Admin',
                'email' => 'admin@gmail.com',
                'password' => 'Admin123!',
                'role' => 'admin',
            ],
            [
                'name' => 'Maria Doctor',
                'email' => 'doctor@gmail.com',
                'password' => 'Doctor123!',
                'role' => 'doctor',
            ],
            [
                'name' => 'Sarah Nurse',
                'email' => 'nurse@gmail.com',
                'password' => 'Nurse123!',
                'role' => 'nurse',
            ],
            [
                'name' => 'Michael LabTechnician',
                'email' => 'lab_technician@gmail.com',
                'password' => 'Lab_technician123!',
                'role' => 'lab_technician',
            ],
            [
                'name' => 'Anna Cashier',
                'email' => 'cashier@gmail.com',
                'password' => 'Cashier123!',
                'role' => 'cashier',
            ],
            [
                'name' => 'David Inventory',
                'email' => 'inventory@gmail.com',
                'password' => 'Inventory123!',
                'role' => 'inventory',
            ],
            [
                'name' => 'Lisa Pharmacy',
                'email' => 'pharmacy@gmail.com',
                'password' => 'Pharmacy123!',
                'role' => 'pharmacy',
            ],
            [
                'name' => 'Robert Billing',
                'email' => 'billing@gmail.com',
                'password' => 'Billing123!',
                'role' => 'billing',
            ],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}