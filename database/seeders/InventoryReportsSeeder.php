<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        \App\Models\StockPrice::truncate();

        // Sample inventory data with various scenarios for reporting
        $sampleData = [
            // In stock items
            [
                'item_code' => 'PARA001',
                'generic_name' => 'Paracetamol',
                'brand_name' => 'Biogesic',
                'price' => 5.50,
                'quantity' => 150,
                'reorder_level' => 50,
                'expiry_date' => now()->addMonths(8),
                'supplier' => 'PharmaCorp',
                'batch_number' => 'BG2024001',
                'date_received' => now()->subDays(30),
            ],
            [
                'item_code' => 'IBU001',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Advil',
                'price' => 8.75,
                'quantity' => 85,
                'reorder_level' => 40,
                'expiry_date' => now()->addMonths(6),
                'supplier' => 'MediSupply',
                'batch_number' => 'AD2024002',
                'date_received' => now()->subDays(20),
            ],
            
            // Low stock items
            [
                'item_code' => 'AMOX001',
                'generic_name' => 'Amoxicillin',
                'brand_name' => 'Amoxil',
                'price' => 15.25,
                'quantity' => 8, // Below reorder level
                'reorder_level' => 25,
                'expiry_date' => now()->addMonths(4),
                'supplier' => 'PharmaCorp',
                'batch_number' => 'AM2024003',
                'date_received' => now()->subDays(45),
            ],
            [
                'item_code' => 'CEPH001',
                'generic_name' => 'Cephalexin',
                'brand_name' => 'Keflex',
                'price' => 22.50,
                'quantity' => 3, // Very low stock
                'reorder_level' => 15,
                'expiry_date' => now()->addMonths(5),
                'supplier' => 'MediSupply',
                'batch_number' => 'KF2024004',
                'date_received' => now()->subDays(60),
            ],
            
            // Out of stock items
            [
                'item_code' => 'ASP001',
                'generic_name' => 'Aspirin',
                'brand_name' => 'Bayer',
                'price' => 3.25,
                'quantity' => 0, // Out of stock
                'reorder_level' => 100,
                'expiry_date' => now()->addMonths(3),
                'supplier' => 'PharmaCorp',
                'batch_number' => 'BY2024005',
                'date_received' => now()->subDays(90),
            ],
            [
                'item_code' => 'MET001',
                'generic_name' => 'Metformin',
                'brand_name' => 'Glucophage',
                'price' => 12.75,
                'quantity' => 0, // Out of stock
                'reorder_level' => 30,
                'expiry_date' => now()->addMonths(7),
                'supplier' => 'MediSupply',
                'batch_number' => 'GL2024006',
                'date_received' => now()->subDays(75),
            ],
            
            // Expiring soon items
            [
                'item_code' => 'VIT001',
                'generic_name' => 'Vitamin C',
                'brand_name' => 'Enervon-C',
                'price' => 6.50,
                'quantity' => 45,
                'reorder_level' => 20,
                'expiry_date' => now()->addDays(15), // Expiring in 15 days
                'supplier' => 'VitaminCorp',
                'batch_number' => 'EN2024007',
                'date_received' => now()->subDays(120),
            ],
            [
                'item_code' => 'CALC001',
                'generic_name' => 'Calcium Carbonate',
                'brand_name' => 'Caltrate',
                'price' => 9.25,
                'quantity' => 25,
                'reorder_level' => 15,
                'expiry_date' => now()->addDays(5), // Expiring very soon
                'supplier' => 'VitaminCorp',
                'batch_number' => 'CT2024008',
                'date_received' => now()->subDays(150),
            ],
            
            // Expired items
            [
                'item_code' => 'IRON001',
                'generic_name' => 'Iron Supplement',
                'brand_name' => 'Ferrous Sulfate',
                'price' => 4.75,
                'quantity' => 12,
                'reorder_level' => 20,
                'expiry_date' => now()->subDays(10), // Already expired
                'supplier' => 'VitaminCorp',
                'batch_number' => 'FS2024009',
                'date_received' => now()->subDays(180),
            ],
            [
                'item_code' => 'ZINC001',
                'generic_name' => 'Zinc Supplement',
                'brand_name' => 'Zincovit',
                'price' => 7.50,
                'quantity' => 18,
                'reorder_level' => 25,
                'expiry_date' => now()->subDays(30), // Expired a month ago
                'supplier' => 'VitaminCorp',
                'batch_number' => 'ZV2024010',
                'date_received' => now()->subDays(200),
            ],
            
            // High value items
            [
                'item_code' => 'INS001',
                'generic_name' => 'Insulin',
                'brand_name' => 'Lantus',
                'price' => 85.00,
                'quantity' => 25,
                'reorder_level' => 10,
                'expiry_date' => now()->addMonths(2),
                'supplier' => 'DiabetesCare',
                'batch_number' => 'LT2024011',
                'date_received' => now()->subDays(15),
            ],
        ];

        foreach ($sampleData as $item) {
            \App\Models\StockPrice::create($item);
        }

        $this->command->info('Sample inventory data with expiry dates created successfully!');
    }
}
