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
                'name' => 'Claire',
                'email' => 'lab_technician@gmail.com',
                'password' => 'Lab_technician123!',
                'role' => 'lab_technician',
            ],
            [
                'name' => 'Rogelio',
                'email' => 'cashier@gmail.com',
                'password' => 'Cashier123!',
                'role' => 'cashier',
            ],
            [
                'name' => 'Melvin',
                'email' => 'inventory@gmail.com',
                'password' => 'Inventory123!',
                'role' => 'inventory',
            ],
            [
                'name' => 'Carl',
                'email' => 'pharmacy@gmail.com',
                'password' => 'Pharmacy123!',
                'role' => 'pharmacy',
            ],
            [
                'name' => 'Laurence',
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