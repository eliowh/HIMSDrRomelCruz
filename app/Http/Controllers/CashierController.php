<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Report;

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

    public function billing(Request $request)
    {
        $query = Billing::with('patient');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('billing_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patient', function($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'LIKE', "%{$search}%")
                                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                                  ->orWhere('patient_no', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }
        
        $billings = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Append search parameters to pagination links
        $billings->appends($request->only(['search', 'status']));
        
        return view('cashier.cashier_billing', compact('billings'));
    }

    public function viewBilling($id)
    {
        $billing = Billing::with(['patient', 'billingItems'])->findOrFail($id);
        // Audit: Log cashier viewing a billing
        try {
            Report::log('Billing Viewed', Report::TYPE_USER_REPORT, 'Cashier viewed billing details', [
                'billing_id' => $billing->id,
                'billing_number' => $billing->billing_number,
                'patient_id' => $billing->patient_id ?? null,
                'viewed_by' => auth()->id(),
                'viewed_at' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to create cashier billing view audit: ' . $e->getMessage());
        }
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

            // Audit: Log billing payment
            try {
                Report::log('Billing Paid', Report::TYPE_USER_REPORT, 'Billing payment processed', [
                    'billing_id' => $billing->id,
                    'billing_number' => $billing->billing_number,
                    'patient_id' => $billing->patient_id,
                    'payment_amount' => $paymentAmount,
                    'change' => $change,
                    'processed_by' => auth()->id(),
                    'payment_date' => now()->toDateTimeString(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create billing payment audit: ' . $e->getMessage());
            }

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
        
        $logoData = $this->getLogoSafely();
        // Audit: Log cashier viewing receipt
        try {
            Report::log('Receipt Viewed', Report::TYPE_USER_REPORT, 'Cashier viewed billing receipt', [
                'billing_id' => $billing->id,
                'billing_number' => $billing->billing_number,
                'patient_id' => $billing->patient_id ?? null,
                'viewed_by' => auth()->id(),
                'viewed_at' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to create cashier receipt view audit: ' . $e->getMessage());
        }

        $pdf = Pdf::loadView('billing.receipt', compact('billing', 'logoData'));
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
        
        $logoData = $this->getLogoSafely();
        // Audit: Log cashier downloading receipt
        try {
            Report::log('Receipt Downloaded', Report::TYPE_USER_REPORT, 'Cashier downloaded billing receipt', [
                'billing_id' => $billing->id,
                'billing_number' => $billing->billing_number,
                'patient_id' => $billing->patient_id ?? null,
                'downloaded_by' => auth()->id(),
                'downloaded_at' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to create cashier receipt download audit: ' . $e->getMessage());
        }

        $pdf = Pdf::loadView('billing.receipt', compact('billing', 'logoData'));
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

    private function getLogoSafely()
    {
        try {
            $logoPath = public_path('img/hospital_logo.jpg');
            
            // Quick checks before processing
            if (!file_exists($logoPath)) {
                return null;
            }
            
            $fileSize = @filesize($logoPath);
            if (!$fileSize || $fileSize > 300000) { // Max 300KB
                return null;
            }
            
            // Try to read the file
            $imageData = @file_get_contents($logoPath);
            if ($imageData === false || strlen($imageData) === 0) {
                return null;
            }
            
            // Create base64 data URL for JPEG
            return 'data:image/jpeg;base64,' . base64_encode($imageData);
            
        } catch (\Throwable $e) {
            // Silently fail and return null
            return null;
        }
    }
}
