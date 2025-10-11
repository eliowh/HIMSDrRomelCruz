<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CashierController extends Controller
{
    public function home(Request $request)
    {
        $filter = $request->get('filter', 'week'); // default to week
        
        // Calculate date range based on filter
        $startDate = match($filter) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subWeek()
        };
        
        // Get statistics for the specified period
        $stats = $this->getPaymentStatistics($startDate, now());
        $stats['filter'] = $filter;
        
        return view('cashier.cashier_home', compact('stats'));
    }

    public function billing()
    {
        // Get all billings with patient information
        $billings = Billing::with('patient')->orderBy('created_at', 'desc')->get();
        
        return view('cashier.cashier_billing', compact('billings'));
    }

    public function viewBilling($id)
    {
        $billing = Billing::with(['patient', 'billingItems'])->findOrFail($id);
        return view('cashier.cashier_billing_view', compact('billing'));
    }

    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01'
        ]);
        
        try {
            $billing = Billing::findOrFail($id);
            
            // Prevent marking already paid billings
            if ($billing->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This billing is already marked as paid and cannot be modified.'
                ], 400);
            }
            
            $paymentAmount = $request->payment_amount;
            $netAmount = $billing->net_amount ?? 0;
            
            // Calculate change
            $change = $paymentAmount - $netAmount;
            
            if ($change < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount is insufficient. Required: ₱' . number_format($netAmount, 2)
                ], 400);
            }
            
            // Update billing status, payment date, and payment details
            $billing->update([
                'status' => 'paid',
                'payment_date' => now(),
                'payment_amount' => $paymentAmount,
                'change_amount' => $change,
                'processed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'change' => $change,
                'change_formatted' => '₱' . number_format($change, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    // Unpaid functionality removed for security - preventing theft and fraud

    public function viewReceipt($id)
    {
        $billing = Billing::with(['patient', 'billingItems', 'createdBy'])->findOrFail($id);
        
        // Only allow receipt viewing for paid billings
        if ($billing->status !== 'paid') {
            return redirect()->back()->with('error', 'Receipt can only be viewed for paid billings.');
        }
        
        $pdf = Pdf::loadView('billing.receipt', compact('billing'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('billing-receipt-' . $billing->billing_number . '.pdf');
    }

    public function downloadReceipt($id)
    {
        $billing = Billing::with(['patient', 'billingItems', 'createdBy'])->findOrFail($id);
        
        // Only allow receipt download for paid billings
        if ($billing->status !== 'paid') {
            return redirect()->back()->with('error', 'Receipt can only be downloaded for paid billings.');
        }
        
        $pdf = Pdf::loadView('billing.receipt', compact('billing'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('billing-receipt-' . $billing->billing_number . '.pdf');
    }
    
    private function getPaymentStatistics($startDate, $endDate)
    {
        // Get paid billings in date range
        $paidBillings = Billing::where('status', 'paid')
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        return [
            'total_payments' => $paidBillings->count(),
            'total_amount' => $paidBillings->sum('net_amount') ?? 0,
            'average_payment' => $paidBillings->count() > 0 ? ($paidBillings->sum('net_amount') / $paidBillings->count()) : 0,
            'pending_billings' => Billing::where('status', 'pending')->count(),
            'recent_payments' => Billing::with('patient')
                ->where('status', 'paid')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->orderBy('payment_date', 'desc')
                ->take(10)
                ->get()
        ];
    }
}
