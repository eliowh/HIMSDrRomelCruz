<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RecalculateBillingAmounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate net amounts for all billing records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating billing amounts...');
        
        \App\Models\Billing::chunk(10, function($billings) {
            foreach($billings as $billing) {
                $billing->load('billingItems');
                
                $philhealthDeduction = $billing->calculatePhilhealthDeduction();
                $seniorPwdDiscount = $billing->calculateSeniorPwdDiscount();
                $netAmount = $billing->calculateNetAmount();
                
                $billing->update([
                    'philhealth_deduction' => $philhealthDeduction,
                    'senior_pwd_discount' => $seniorPwdDiscount,
                    'net_amount' => $netAmount
                ]);
                
                $this->line("Updated Billing ID {$billing->id} - Net Amount: ₱" . number_format($netAmount, 2));
            }
        });
        
        $this->info('All billing records have been recalculated!');
    }
}
