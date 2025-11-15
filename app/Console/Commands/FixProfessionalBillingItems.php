<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixProfessionalBillingItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:fix-professional-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix existing professional billing items to include case rates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing professional billing items...');
        
        $professionalItems = \App\Models\BillingItem::where('item_type', 'professional')->get();
        
        foreach ($professionalItems as $item) {
            if ($item->icd_code) {
                // Get the ICD rate information
                $icd = \App\Models\Icd10NamePriceRate::where('COL 1', $item->icd_code)->first();
                if ($icd) {
                    // Extract case rate from COL 3
                    $rawCaseRate = $icd->getAttributes()['COL 3'] ?? '0';
                    $cleanCaseRate = is_string($rawCaseRate) ? str_replace(',', '', $rawCaseRate) : $rawCaseRate;
                    $caseRate = is_numeric($cleanCaseRate) ? (float)$cleanCaseRate : 0.00;
                    
                    // Professional fee is already in unit_price (COL 4)
                    $professionalFee = (float)$item->unit_price;
                    
                    // Update the item with case rate. The billed total should only include professional fee (case rate is a coverage/discount)
                    $newTotal = $item->quantity * $professionalFee;

                    $item->update([
                        'case_rate' => $caseRate,
                        'total_amount' => $newTotal
                    ]);

                    $this->line("Updated Item ID {$item->id} - Case Rate: ₱" . number_format($caseRate, 2) . ", Professional Fee: ₱" . number_format($professionalFee, 2) . ", New Total: ₱" . number_format($newTotal, 2));
                }
            }
        }
        
        // Now recalculate the billing totals
        \App\Models\Billing::chunk(10, function($billings) {
            foreach($billings as $billing) {
                $billing->load('billingItems');
                
                // Recalculate totals from items
                $totalAmount = $billing->billingItems->sum('total_amount');
                $professionalFees = $billing->billingItems->where('item_type', 'professional')->sum('total_amount');

                // PhilHealth deduction should be derived from case_rate values and not from totals
                $philhealthDeduction = 0;
                if ($billing->is_philhealth_member) {
                    foreach ($billing->billingItems->where('item_type', 'professional') as $bi) {
                        $philhealthDeduction += ($bi->case_rate * ($bi->quantity ?: 1));
                    }
                }
                $seniorPwdDiscount = $billing->calculateSeniorPwdDiscount();
                $netAmount = $totalAmount - $philhealthDeduction - $seniorPwdDiscount;
                
                $billing->update([
                    'total_amount' => $totalAmount,
                    'professional_fees' => $professionalFees,
                    'philhealth_deduction' => $philhealthDeduction,
                    'senior_pwd_discount' => $seniorPwdDiscount,
                    'net_amount' => $netAmount
                ]);
                
                $this->line("Updated Billing ID {$billing->id} - Total: ₱" . number_format($totalAmount, 2) . ", Net: ₱" . number_format($netAmount, 2));
            }
        });
        
        $this->info('All professional billing items have been fixed!');
    }
}
