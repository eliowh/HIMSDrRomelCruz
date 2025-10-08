<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Icd10PriceRate;

class Icd10PriceRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $icdRates = [
            // Infectious diseases
            [
                'icd_code' => 'A00.0',
                'description' => 'Cholera due to Vibrio cholerae 01, biovar cholerae',
                'professional_fee' => 5000.00,
                'room_rate_per_day' => 2500.00,
                'medicine_allowance' => 3000.00,
                'lab_fee' => 1500.00,
                'philhealth_coverage_percentage' => 60.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'A15.0',
                'description' => 'Tuberculosis of lung',
                'professional_fee' => 7500.00,
                'room_rate_per_day' => 3000.00,
                'medicine_allowance' => 5000.00,
                'lab_fee' => 2500.00,
                'philhealth_coverage_percentage' => 80.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'B34.9',
                'description' => 'Viral infection, unspecified',
                'professional_fee' => 2500.00,
                'room_rate_per_day' => 1500.00,
                'medicine_allowance' => 2000.00,
                'lab_fee' => 1000.00,
                'philhealth_coverage_percentage' => 50.00,
                'is_active' => true
            ],

            // Cardiovascular diseases
            [
                'icd_code' => 'I21.9',
                'description' => 'Acute myocardial infarction, unspecified',
                'professional_fee' => 15000.00,
                'room_rate_per_day' => 5000.00,
                'medicine_allowance' => 10000.00,
                'lab_fee' => 5000.00,
                'philhealth_coverage_percentage' => 70.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'I10',
                'description' => 'Essential (primary) hypertension',
                'professional_fee' => 3000.00,
                'room_rate_per_day' => 2000.00,
                'medicine_allowance' => 2500.00,
                'lab_fee' => 1200.00,
                'philhealth_coverage_percentage' => 60.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'I50.9',
                'description' => 'Heart failure, unspecified',
                'professional_fee' => 8000.00,
                'room_rate_per_day' => 3500.00,
                'medicine_allowance' => 6000.00,
                'lab_fee' => 3000.00,
                'philhealth_coverage_percentage' => 65.00,
                'is_active' => true
            ],

            // Respiratory diseases
            [
                'icd_code' => 'J18.9',
                'description' => 'Pneumonia, unspecified organism',
                'professional_fee' => 6000.00,
                'room_rate_per_day' => 2800.00,
                'medicine_allowance' => 4000.00,
                'lab_fee' => 2000.00,
                'philhealth_coverage_percentage' => 70.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'J44.1',
                'description' => 'Chronic obstructive pulmonary disease with acute exacerbation',
                'professional_fee' => 7000.00,
                'room_rate_per_day' => 3200.00,
                'medicine_allowance' => 5000.00,
                'lab_fee' => 2500.00,
                'philhealth_coverage_percentage' => 65.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'J06.9',
                'description' => 'Acute upper respiratory infection, unspecified',
                'professional_fee' => 1500.00,
                'room_rate_per_day' => 1200.00,
                'medicine_allowance' => 1000.00,
                'lab_fee' => 800.00,
                'philhealth_coverage_percentage' => 40.00,
                'is_active' => true
            ],

            // Digestive diseases
            [
                'icd_code' => 'K35.9',
                'description' => 'Acute appendicitis, unspecified',
                'professional_fee' => 12000.00,
                'room_rate_per_day' => 4000.00,
                'medicine_allowance' => 7000.00,
                'lab_fee' => 3500.00,
                'philhealth_coverage_percentage' => 75.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'K29.7',
                'description' => 'Gastritis, unspecified',
                'professional_fee' => 2000.00,
                'room_rate_per_day' => 1500.00,
                'medicine_allowance' => 1500.00,
                'lab_fee' => 1000.00,
                'philhealth_coverage_percentage' => 50.00,
                'is_active' => true
            ],

            // Endocrine diseases
            [
                'icd_code' => 'E11.9',
                'description' => 'Type 2 diabetes mellitus without complications',
                'professional_fee' => 4000.00,
                'room_rate_per_day' => 2200.00,
                'medicine_allowance' => 3500.00,
                'lab_fee' => 1800.00,
                'philhealth_coverage_percentage' => 60.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'E78.5',
                'description' => 'Hyperlipidemia, unspecified',
                'professional_fee' => 2500.00,
                'room_rate_per_day' => 1800.00,
                'medicine_allowance' => 2000.00,
                'lab_fee' => 1200.00,
                'philhealth_coverage_percentage' => 45.00,
                'is_active' => true
            ],

            // Pregnancy and childbirth
            [
                'icd_code' => 'O80',
                'description' => 'Encounter for full-term uncomplicated delivery',
                'professional_fee' => 20000.00,
                'room_rate_per_day' => 4500.00,
                'medicine_allowance' => 8000.00,
                'lab_fee' => 4000.00,
                'philhealth_coverage_percentage' => 80.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'O82',
                'description' => 'Encounter for cesarean delivery without indication',
                'professional_fee' => 35000.00,
                'room_rate_per_day' => 6000.00,
                'medicine_allowance' => 12000.00,
                'lab_fee' => 6000.00,
                'philhealth_coverage_percentage' => 85.00,
                'is_active' => true
            ],

            // Injury and trauma
            [
                'icd_code' => 'S72.0',
                'description' => 'Fracture of neck of femur',
                'professional_fee' => 25000.00,
                'room_rate_per_day' => 5500.00,
                'medicine_allowance' => 10000.00,
                'lab_fee' => 5000.00,
                'philhealth_coverage_percentage' => 70.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'T14.9',
                'description' => 'Injury, unspecified',
                'professional_fee' => 3500.00,
                'room_rate_per_day' => 2000.00,
                'medicine_allowance' => 2500.00,
                'lab_fee' => 1500.00,
                'philhealth_coverage_percentage' => 55.00,
                'is_active' => true
            ],

            // Mental health
            [
                'icd_code' => 'F32.9',
                'description' => 'Major depressive disorder, single episode, unspecified',
                'professional_fee' => 4500.00,
                'room_rate_per_day' => 2800.00,
                'medicine_allowance' => 3000.00,
                'lab_fee' => 1000.00,
                'philhealth_coverage_percentage' => 50.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'F10.2',
                'description' => 'Alcohol dependence syndrome',
                'professional_fee' => 6000.00,
                'room_rate_per_day' => 3000.00,
                'medicine_allowance' => 4000.00,
                'lab_fee' => 2000.00,
                'philhealth_coverage_percentage' => 40.00,
                'is_active' => true
            ],

            // General symptoms
            [
                'icd_code' => 'R50.9',
                'description' => 'Fever, unspecified',
                'professional_fee' => 1800.00,
                'room_rate_per_day' => 1200.00,
                'medicine_allowance' => 1200.00,
                'lab_fee' => 800.00,
                'philhealth_coverage_percentage' => 40.00,
                'is_active' => true
            ],
            [
                'icd_code' => 'R06.0',
                'description' => 'Dyspnea',
                'professional_fee' => 2200.00,
                'room_rate_per_day' => 1500.00,
                'medicine_allowance' => 1800.00,
                'lab_fee' => 1200.00,
                'philhealth_coverage_percentage' => 45.00,
                'is_active' => true
            ]
        ];

        foreach ($icdRates as $rate) {
            Icd10PriceRate::create($rate);
        }
    }
}
