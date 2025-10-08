<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhilhealthMember;
use Carbon\Carbon;

class PhilhealthMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'philhealth_number' => '12-345678901-2',
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'birth_date' => '1985-03-15',
                'member_type' => 'Active',
                'category' => 'Direct Contributor',
                'premium_amount' => 5000.00,
                'effectivity_date' => '2023-01-01',
                'expiry_date' => '2025-12-31',
                'employer' => 'ABC Corporation',
                'address' => '123 Main St, Manila',
                'status' => 'active'
            ],
            [
                'philhealth_number' => '12-987654321-5',
                'first_name' => 'Maria',
                'middle_name' => 'Garcia',
                'last_name' => 'Rodriguez',
                'birth_date' => '1978-07-22',
                'member_type' => 'Active',
                'category' => 'Indirect Contributor',
                'premium_amount' => 3000.00,
                'effectivity_date' => '2024-01-01',
                'expiry_date' => '2025-12-31',
                'employer' => null,
                'address' => '456 Oak Ave, Quezon City',
                'status' => 'active'
            ],
            [
                'philhealth_number' => '12-555666777-8',
                'first_name' => 'Pedro',
                'middle_name' => 'Lim',
                'last_name' => 'Gonzales',
                'birth_date' => '1955-11-08',
                'member_type' => 'Lifetime',
                'category' => 'Senior Citizen',
                'premium_amount' => 0.00,
                'effectivity_date' => '2023-11-08',
                'expiry_date' => '2025-12-31',
                'employer' => null,
                'address' => '789 Pine St, Makati',
                'status' => 'active'
            ],
            [
                'philhealth_number' => '12-111222333-4',
                'first_name' => 'Ana',
                'middle_name' => 'Cruz',
                'last_name' => 'Reyes',
                'birth_date' => '1990-05-30',
                'member_type' => 'Active',
                'category' => 'PWD Member',
                'premium_amount' => 2500.00,
                'effectivity_date' => '2024-06-01',
                'expiry_date' => '2025-12-31',
                'employer' => null,
                'address' => '321 Elm St, Pasig',
                'status' => 'active'
            ],
            [
                'philhealth_number' => '12-777888999-0',
                'first_name' => 'Jose',
                'middle_name' => 'Alberto',
                'last_name' => 'Mendoza',
                'birth_date' => '1982-12-01',
                'member_type' => 'Active',
                'category' => 'Direct Contributor',
                'premium_amount' => 4500.00,
                'effectivity_date' => '2023-01-01',
                'expiry_date' => '2024-06-30',
                'employer' => 'XYZ Industries',
                'address' => '654 Maple Dr, Taguig',
                'status' => 'inactive'
            ]
        ];

        foreach ($members as $member) {
            PhilhealthMember::create($member);
        }
    }
}
