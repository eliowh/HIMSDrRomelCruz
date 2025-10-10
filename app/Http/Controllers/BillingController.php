<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Patient;
use App\Models\PhilhealthMember;
use App\Models\Icd10NamePriceRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billing::with(['patient', 'createdBy'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);
        
        return view('billing.index', compact('billings'));
    }

    public function create()
    {
        $patients = Patient::orderBy('first_name')->get();
        $icdRates = Icd10NamePriceRate::getAllCodes();
        
        return view('billing.create', compact('patients', 'icdRates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'billing_items' => 'required|array|min:1',
            'billing_items.*.item_type' => 'required|in:room,medicine,laboratory,professional,other',
            'billing_items.*.description' => 'required|string',
            'billing_items.*.quantity' => 'required|numeric|min:0.01',
            'billing_items.*.unit_price' => 'required|numeric|min:0',
            'billing_items.*.case_rate' => 'nullable|numeric|min:0',
            'billing_items.*.icd_code' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            $patient = Patient::findOrFail($request->patient_id);
            
            // Check PhilHealth membership - prioritize user input over automatic lookup
            $isPhilhealthMember = $request->boolean('is_philhealth_member');
            
            // If checkbox is not checked, fall back to automatic lookup
            if (!$isPhilhealthMember) {
                $philhealthMember = PhilhealthMember::findByPatient($patient);
                $isPhilhealthMember = $philhealthMember && $philhealthMember->isEligibleForCoverage();
            }
            
            // Generate billing number
            $billingNumber = 'BILL-' . date('Y') . '-' . str_pad(Billing::whereYear('created_at', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
            
            // Calculate totals
            $totalAmount = 0;
            $roomCharges = 0;
            $professionalFees = 0;
            $medicineCharges = 0;
            $labCharges = 0;
            $otherCharges = 0;
            
            foreach ($request->billing_items as $item) {
                // For professional items, add both case rate and professional fee
                if ($item['item_type'] === 'professional') {
                    $caseRate = (float)($item['case_rate'] ?? 0);
                    $professionalFee = (float)($item['unit_price'] ?? 0);
                    $itemTotal = $item['quantity'] * ($caseRate + $professionalFee);
                } else {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                }
                
                $totalAmount += $itemTotal;
                
                switch ($item['item_type']) {
                    case 'room':
                        $roomCharges += $itemTotal;
                        break;
                    case 'professional':
                        $professionalFees += $itemTotal;
                        break;
                    case 'medicine':
                        $medicineCharges += $itemTotal;
                        break;
                    case 'laboratory':
                        $labCharges += $itemTotal;
                        break;
                    case 'other':
                        $otherCharges += $itemTotal;
                        break;
                }
            }
            
            // Create billing record
            $billing = Billing::create([
                'patient_id' => $patient->id,
                'billing_number' => $billingNumber,
                'total_amount' => $totalAmount,
                'room_charges' => $roomCharges,
                'professional_fees' => $professionalFees,
                'medicine_charges' => $medicineCharges,
                'lab_charges' => $labCharges,
                'other_charges' => $otherCharges,
                'is_philhealth_member' => $isPhilhealthMember,
                'is_senior_citizen' => $request->boolean('is_senior_citizen'),
                'is_pwd' => $request->boolean('is_pwd'),
                'billing_date' => Carbon::now(),
                'status' => 'pending',
                'created_by' => auth()->id(),
                'notes' => $request->notes
            ]);
            
            // Create billing items
            foreach ($request->billing_items as $item) {
                // Calculate correct total amount based on item type
                if ($item['item_type'] === 'professional') {
                    $caseRate = (float)($item['case_rate'] ?? 0);
                    $professionalFee = (float)($item['unit_price'] ?? 0);
                    $itemTotalAmount = $item['quantity'] * ($caseRate + $professionalFee);
                } else {
                    $itemTotalAmount = $item['quantity'] * $item['unit_price'];
                }
                
                BillingItem::create([
                    'billing_id' => $billing->id,
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'case_rate' => $item['case_rate'] ?? null,
                    'total_amount' => $itemTotalAmount,
                    'icd_code' => $item['icd_code'] ?? null,
                    'date_charged' => Carbon::now()
                ]);
            }
            
            // Refresh the billing model to load the newly created items
            $billing->refresh();
            $billing->load('billingItems');
            
            // Calculate deductions and discounts
            $philhealthDeduction = $billing->calculatePhilhealthDeduction();
            $seniorPwdDiscount = $billing->calculateSeniorPwdDiscount();
            $netAmount = $billing->calculateNetAmount();
            
            // Update billing with calculations
            $billing->update([
                'philhealth_deduction' => $philhealthDeduction,
                'senior_pwd_discount' => $seniorPwdDiscount,
                'net_amount' => $netAmount
            ]);
            
            DB::commit();
            
            return redirect()->route('billing.show', $billing)
                           ->with('success', 'Billing created successfully.');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create billing: ' . $e->getMessage()]);
        }
    }

    public function show(Billing $billing)
    {
        $billing->load(['patient', 'billingItems', 'createdBy']);
        
        // Recalculate totals to ensure accuracy
        $billing->recalculateFromItems();
        $billing->save();
        
        return view('billing.show', compact('billing'));
    }

    public function edit(Billing $billing)
    {
        $billing->load(['billingItems', 'patient', 'createdBy']);
        
        // Recalculate totals to ensure accuracy before editing
        $billing->recalculateFromItems();
        $billing->save();
        
        $patients = Patient::orderBy('first_name')->get();
        $icdRates = Icd10NamePriceRate::getAllCodes();
        
        return view('billing.edit', compact('billing', 'patients', 'icdRates'));
    }

    public function update(Request $request, Billing $billing)
    {
        $request->validate([
            'professional_fees' => 'nullable|numeric|min:0',
            'is_philhealth_member' => 'boolean',
            'is_senior_citizen' => 'boolean',
            'is_pwd' => 'boolean',
            'status' => 'required|in:pending,paid,cancelled',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Only update professional fees if provided and different
            if ($request->filled('professional_fees') && $request->professional_fees != $billing->professional_fees) {
                // Get current professional items to preserve case rate structure
                $professionalItems = $billing->billingItems()->where('item_type', 'professional')->get();
                
                if ($professionalItems->count() > 0) {
                    foreach ($professionalItems as $item) {
                        // The case rate should remain the same, only professional fee portion changes
                        $caseRate = $item->case_rate ?? 0;
                        $newProfessionalFee = $request->professional_fees;
                        $newTotalAmount = $caseRate + $newProfessionalFee;
                        
                        $item->update([
                            'unit_price' => $newProfessionalFee,
                            'total_amount' => $newTotalAmount
                        ]);
                    }
                }
            }
            
            // Recalculate totals from actual billing items
            $roomCharges = $billing->billingItems()->where('item_type', 'room')->sum('total_amount');
            $professionalTotal = $billing->billingItems()->where('item_type', 'professional')->sum('total_amount');
            $medicineCharges = $billing->billingItems()->where('item_type', 'medicine')->sum('total_amount');
            $labCharges = $billing->billingItems()->where('item_type', 'laboratory')->sum('total_amount');
            $otherCharges = $billing->billingItems()->where('item_type', 'other')->sum('total_amount');
            
            $totalAmount = $roomCharges + $professionalTotal + $medicineCharges + $labCharges + $otherCharges;
            
            // Calculate deductions before updating
            $tempBilling = clone $billing;
            $tempBilling->total_amount = $totalAmount;
            $tempBilling->is_philhealth_member = $request->boolean('is_philhealth_member');
            $tempBilling->is_senior_citizen = $request->boolean('is_senior_citizen');
            $tempBilling->is_pwd = $request->boolean('is_pwd');
            
            $philhealthDeduction = $tempBilling->calculatePhilhealthDeduction();
            $seniorPwdDiscount = $tempBilling->calculateSeniorPwdDiscount();
            $netAmount = $totalAmount - $philhealthDeduction - $seniorPwdDiscount;
            
            // Update billing record with all calculated values
            $billing->update([
                'room_charges' => $roomCharges,
                'professional_fees' => $professionalTotal,
                'medicine_charges' => $medicineCharges,
                'lab_charges' => $labCharges,
                'other_charges' => $otherCharges,
                'total_amount' => $totalAmount,
                'philhealth_deduction' => $philhealthDeduction,
                'senior_pwd_discount' => $seniorPwdDiscount,
                'net_amount' => $netAmount,
                'is_philhealth_member' => $request->boolean('is_philhealth_member'),
                'is_senior_citizen' => $request->boolean('is_senior_citizen'),
                'is_pwd' => $request->boolean('is_pwd'),
                'status' => $request->status,
                'notes' => $request->notes
            ]);
            
            DB::commit();
            
            return redirect()->route('billing.show', $billing)
                           ->with('success', 'Billing updated successfully.');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update billing: ' . $e->getMessage()]);
        }
    }

    public function destroy(Billing $billing)
    {
        try {
            $billing->billingItems()->delete();
            $billing->delete();
            
            return redirect()->route('billing.index')
                           ->with('success', 'Billing deleted successfully.');
                           
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete billing: ' . $e->getMessage()]);
        }
    }

    public function checkPhilhealth(Request $request)
    {
        $patient = Patient::findOrFail($request->patient_id);
        $member = PhilhealthMember::findByPatient($patient);
        
        return response()->json([
            'is_member' => $member && $member->isEligibleForCoverage(),
            'member_info' => $member ? [
                'philhealth_number' => $member->philhealth_number,
                'member_type' => $member->getFormattedMemberType(),
                'status' => $member->getFormattedMembershipStatus(),
                'expiry_date' => $member->expiry_date->format('M d, Y')
            ] : null
        ]);
    }

    public function getIcdRates(Request $request)
    {
        $rates = Icd10NamePriceRate::search($request->query);
        
        return response()->json($rates);
    }

    public function exportReceipt(Billing $billing)
    {
        $billing->load(['patient', 'billingItems', 'createdBy']);
        
        $pdf = Pdf::loadView('billing.receipt', compact('billing'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('billing-receipt-' . $billing->billing_number . '.pdf');
    }

    /**
     * Get patient services for billing
     */
    public function getPatientServices(Request $request)
    {
        try {
            $patient = Patient::with(['labOrders', 'pharmacyRequests'])->findOrFail($request->patient_id);
            
            $billableServices = $patient->billable_services;
            
            return response()->json([
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->display_name,
                    'patient_no' => $patient->patient_no,
                    'admission_diagnosis' => $patient->admission_diagnosis
                ],
                'services' => $billableServices
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search patients for autocomplete
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['patients' => []]);
        }

        $patients = Patient::where(function($q) use ($query) {
                          $q->where('first_name', 'LIKE', "%{$query}%")
                            ->orWhere('last_name', 'LIKE', "%{$query}%")
                            ->orWhere('patient_no', 'LIKE', "%{$query}%");
                      })
                      ->limit(10)
                      ->get()
                      ->map(function ($patient) {
                          return [
                              'id' => $patient->id,
                              'text' => $patient->display_name . ' (Patient #: ' . $patient->patient_no . ')'
                          ];
                      });

        return response()->json(['patients' => $patients]);
    }

    /**
     * Mark billing as paid
     */
    public function markAsPaid(Billing $billing)
    {
        try {
            $billing->update([
                'status' => 'paid',
                'payment_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Billing marked as paid successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark billing as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark billing as unpaid (revert to pending)
     */
    public function markAsUnpaid(Billing $billing)
    {
        try {
            $billing->update([
                'status' => 'pending',
                'payment_date' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Billing marked as unpaid successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark billing as unpaid: ' . $e->getMessage()
            ], 500);
        }
    }
}
