<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CashierController extends Controller
{
    public function home()
    {
        return view('cashier.cashier_home');
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

    public function markAsPaid($id)
    {
        try {
            $billing = Billing::findOrFail($id);
            
            // Update billing status and payment date
            $billing->update([
                'status' => 'paid',
                'payment_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Billing marked as paid successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark billing as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsUnpaid($id)
    {
        try {
            $billing = Billing::findOrFail($id);
            
            // Update billing status and clear payment date
            $billing->update([
                'status' => 'pending',
                'payment_date' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Billing reverted to unpaid status successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert billing status: ' . $e->getMessage()
            ], 500);
        }
    }

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
}
