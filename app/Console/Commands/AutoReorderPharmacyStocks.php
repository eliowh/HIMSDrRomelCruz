<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PharmacyStock;
use App\Models\Report;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoReorderPharmacyStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pharmacy:auto-reorder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically replenish low pharmacy stocks once per day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting pharmacy auto-reorder...');

        try {
            $lowStockItems = PharmacyStock::whereColumn('quantity', '<=', 'reorder_level')
                ->orderBy('quantity', 'asc')
                ->limit(100)
                ->get();

            if ($lowStockItems->isEmpty()) {
                $this->info('No low-stock items found.');
                return 0;
            }

            $executed = 0;
            foreach ($lowStockItems as $li) {
                try {
                    $reorderAmount = max(intval($li->reorder_level) * 2, 1);
                    $oldQty = intval($li->quantity ?? 0);
                    $li->quantity = $oldQty + $reorderAmount;
                    $li->save();
                    try {
                        Report::log('Auto Reorder Executed', Report::TYPE_SYSTEM_REPORT, 'Auto-increased pharmacy stock due to low level', ['item_code' => $li->item_code, 'added' => $reorderAmount, 'old' => $oldQty]);
                    } catch (\Throwable $e) {
                        Log::error('Report log failed during auto-reorder: ' . $e->getMessage());
                    }
                    $executed++;
                } catch (\Throwable $e) {
                    Log::warning('Auto-reorder failed for item ' . $li->item_code . ': ' . $e->getMessage());
                }
            }

            // Set a cache key to indicate we've executed today (optional safety guard if the command is called manually multiple times)
            $autoReorderKey = 'pharmacy_auto_reorder_' . date('Ymd');
            Cache::put($autoReorderKey, true, Carbon::now()->addHours(24));

            $this->info('Auto-reorder completed. Items updated: ' . $executed);
            return 0;
        } catch (\Throwable $e) {
            Log::error('Auto-reorder command failed: ' . $e->getMessage());
            $this->error('Auto-reorder failed: ' . $e->getMessage());
            return 1;
        }
    }
}
