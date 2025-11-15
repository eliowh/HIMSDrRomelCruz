<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LabOrder;

class CleanLabOrderNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:clean-notes {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up lab orders by moving "Additional:" notes from test_requested to notes field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Searching for lab orders with "Additional:" notes in test_requested field...');
        
        // Find lab orders that have "Additional:" in test_requested
        $labOrders = LabOrder::where('test_requested', 'LIKE', '%Additional:%')->get();
        
        if ($labOrders->isEmpty()) {
            $this->info('✅ No lab orders found with "Additional:" notes in test_requested field.');
            return;
        }
        
        $this->info("📋 Found {$labOrders->count()} lab orders to clean up:");
        
        $cleaned = 0;
        
        foreach ($labOrders as $order) {
            // Split the test_requested field
            $testRequested = $order->test_requested;
            $notes = $order->notes ?? '';
            
            // Look for "Additional:" pattern
            if (preg_match('/^(.+?)\s*\n*\s*Additional:\s*(.+)$/s', $testRequested, $matches)) {
                $cleanTestRequested = trim($matches[1]);
                $additionalNotes = trim($matches[2]);
                
                // Combine with existing notes
                $newNotes = $notes;
                if ($additionalNotes) {
                    if ($newNotes) {
                        $newNotes .= "\n\nAdditional Tests: " . $additionalNotes;
                    } else {
                        $newNotes = "Additional Tests: " . $additionalNotes;
                    }
                }
                
                $this->line("ID #{$order->id}: '{$testRequested}' → '{$cleanTestRequested}'");
                $this->line("  Notes: '{$notes}' → '{$newNotes}'");
                
                if (!$dryRun) {
                    $order->update([
                        'test_requested' => $cleanTestRequested,
                        'notes' => $newNotes
                    ]);
                    $cleaned++;
                }
            }
        }
        
        if ($dryRun) {
            $this->info("\n🔍 Dry run completed. Use --dry-run=false to apply changes.");
        } else {
            $this->info("\n✅ Cleaned up {$cleaned} lab orders successfully!");
        }
        
        return Command::SUCCESS;
    }
}
