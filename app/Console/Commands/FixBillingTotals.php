<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BillingItem;
use App\Models\Billing;

class FixBillingTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:fix-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix billing item total amounts and recalculate billing totals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing billing item totals...');
        
        $items = BillingItem::all();
        $updated = 0;

        foreach ($items as $item) {
            $correctTotal = $item->quantity * $item->unit_price;
            if ($item->total_amount != $correctTotal) {
                $this->line("Fixing item ID {$item->id}: {$item->total_amount} -> {$correctTotal}");
                $item->total_amount = $correctTotal;
                $item->save();
                $updated++;
            }
        }

        $this->info("Updated {$updated} billing items with correct total_amount values.");

        $this->info('Recalculating billing totals...');
        
        $billings = Billing::all();
        foreach ($billings as $billing) {
            $this->line("Recalculating billing ID {$billing->id}");
            $billing->recalculateFromItems();
            $billing->save();
        }

        $this->info('All billing totals have been fixed!');
    }
}
