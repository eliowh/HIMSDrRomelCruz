<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LabOrder;
use App\Models\StockPrice;
use Illuminate\Support\Facades\DB;

class FixLabOrderPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:fix-pricing {--dry-run=true : Run in dry-run mode to preview changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix pricing for lab orders that have 0 or missing prices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run') === 'true';
        
        $this->info('🔍 Searching for lab orders with missing or zero pricing...');
        
        // Get lab orders that need price fixes
        $labOrders = LabOrder::where(function($query) {
            $query->where('price', 0)
                  ->orWhereNull('price');
        })->get();
        
        if ($labOrders->isEmpty()) {
            $this->info('✅ No lab orders need pricing fixes!');
            return;
        }
        
        $this->info("📋 Found {$labOrders->count()} lab orders to fix:");
        
        $fixedCount = 0;
        
        foreach ($labOrders as $order) {
            $price = $this->lookupLabPrice($order->test_requested);
            
            if ($price > 0) {
                $this->line("ID #{$order->id}: '{$order->test_requested}' → ₱{$price}");
                
                if (!$isDryRun) {
                    $order->update(['price' => $price]);
                }
                $fixedCount++;
            } else {
                $this->warn("ID #{$order->id}: '{$order->test_requested}' → No price found");
            }
        }
        
        if ($isDryRun) {
            $this->info("🔍 Dry run completed. {$fixedCount} orders would be fixed.");
            $this->info("Use --dry-run=false to apply changes.");
        } else {
            $this->info("✅ Fixed pricing for {$fixedCount} lab orders!");
        }
    }
    
    /**
     * Look up price for a lab test
     */
    private function lookupLabPrice($testName)
    {
        // Determine which table to check based on test category
        $table = null;
        $searchName = '';
        
        if (stripos($testName, 'XRAY:') === 0) {
            $table = 'xray_prices';
            $searchName = trim(substr($testName, 5)); // Remove "XRAY: " prefix
        } elseif (stripos($testName, 'ULTRASOUND:') === 0) {
            $table = 'ultrasound_prices';
            $searchName = trim(substr($testName, 11)); // Remove "ULTRASOUND: " prefix
        } elseif (stripos($testName, 'LABORATORY:') === 0) {
            $table = 'laboratory_prices';
            $searchName = trim(substr($testName, 11)); // Remove "LABORATORY: " prefix
        } else {
            // For tests without category prefix, assume they are laboratory tests
            $table = 'laboratory_prices';
            $searchName = trim($testName);
        }
        
        // Try to find exact or partial match in the pricing tables
        if ($table && $searchName) {
            try {
                // First try exact match (case insensitive)
                $result = DB::table($table)
                    ->whereRaw('LOWER(TRIM(`COL 1`)) = ?', [strtolower(trim($searchName))])
                    ->first();
                
                if ($result && isset($result->{'COL 2'})) {
                    return (float)str_replace(',', '', $result->{'COL 2'});
                }
                
                // Try partial match
                $result = DB::table($table)
                    ->whereRaw('LOWER(`COL 1`) LIKE ?', ['%' . strtolower($searchName) . '%'])
                    ->first();
                
                if ($result && isset($result->{'COL 2'})) {
                    return (float)str_replace(',', '', $result->{'COL 2'});
                }
                
                // For common test mappings, try specific matches
                $mappings = $this->getTestMappings($table);
                $lowerSearchName = strtolower($searchName);
                
                foreach ($mappings as $pattern => $dbName) {
                    if (stripos($lowerSearchName, strtolower($pattern)) !== false) {
                        $result = DB::table($table)
                            ->whereRaw('LOWER(TRIM(`COL 1`)) = ?', [strtolower($dbName)])
                            ->first();
                        
                        if ($result && isset($result->{'COL 2'})) {
                            return (float)str_replace(',', '', $result->{'COL 2'});
                        }
                    }
                }
                
            } catch (\Exception $e) {
                $this->warn("Database error for table {$table}: " . $e->getMessage());
            }
        }
        
        return 0;
    }
    
    /**
     * Get test name mappings for common procedures
     */
    private function getTestMappings($table)
    {
        if ($table === 'xray_prices') {
            return [
                'Chest X-Ray' => 'Chest',
                'Spine X-Ray' => 'Spinal', 
                'Head/Skull' => 'Head/Skull',
                'Pelvis X-Ray' => 'Pelvic',
                'Ankle X-Ray' => 'Extremity',
                'Bone' => 'Bone',
                'Abdominal' => 'Abdominal'
            ];
        } elseif ($table === 'ultrasound_prices') {
            return [
                'Whole Abdomen' => 'Whole Abdomen',
                'Abdominal Ultrasound' => 'Whole Abdomen',
                'Echocardiogram' => '2DED',
                'Renal Ultrasound' => 'KUB',
                'HBT' => 'HBT',
                'Thyroid' => 'Thyroid',
                'Chest' => 'Chest'
            ];
        } elseif ($table === 'laboratory_prices') {
            return [
                'Liver Function Tests' => 'SGOT',
                'Blood Sugar/Glucose' => 'FBS/RBS',
                'Kidney Function Tests' => 'BUN',
                'ANTI-HCV' => 'ANTI-HCV',
                'AFP' => 'AFP',
                'HS CRP' => 'HS CRP',
                'Complete Blood Count (CBC)' => 'CBC',
                'Complete Blood Count' => 'CBC',
                'Blood Count' => 'CBC',
                'Urinalysis' => 'URINALYSIS'
            ];
        }
        
        return [];
    }
}
