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
        
        return view('billings.index', compact('billings'));
    }

    public function create()
    {
        $patients = Patient::orderBy('first_name')->get();
        $icdRates = Icd10NamePriceRate::getAllCodes();
        
        return view('billings.create', compact('patients', 'icdRates'));
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
            'billing_items.*.icd_code' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            $patient = Patient::findOrFail($request->patient_id);
            
            // Check PhilHealth membership
            $philhealthMember = PhilhealthMember::findByPatient($patient);
            $isPhilhealthMember = $philhealthMember && $philhealthMember->isEligibleForCoverage();
            
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
                $itemTotal = $item['quantity'] * $item['unit_price'];
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
                BillingItem::create([
                    'billing_id' => $billing->id,
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $item['quantity'] * $item['unit_price'],
                    'icd_code' => $item['icd_code'] ?? null,
                    'date_charged' => Carbon::now()
                ]);
            }
            
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
            
            return redirect()->route('billings.show', $billing)
                           ->with('success', 'Billing created successfully.');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create billing: ' . $e->getMessage()]);
        }
    }

    public function show(Billing $billing)
    {
        $billing->load(['patient', 'billingItems.icd10PriceRate', 'createdBy']);
        
        return view('billings.show', compact('billing'));
    }

    public function edit(Billing $billing)
    {
        $billing->load(['billingItems']);
        $patients = Patient::orderBy('first_name')->get();
        $icdRates = Icd10NamePriceRate::getAllCodes();
        
        return view('billings.edit', compact('billing', 'patients', 'icdRates'));
    }

    public function update(Request $request, Billing $billing)
    {
        $request->validate([
            'professional_fees' => 'required|numeric|min:0',
            'is_senior_citizen' => 'boolean',
            'is_pwd' => 'boolean',
            'status' => 'required|in:pending,paid,cancelled',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Update professional fees in billing items
            $billing->billingItems()
                   ->where('item_type', 'professional')
                   ->update(['unit_price' => $request->professional_fees]);
            
            // Recalculate totals
            $professionalTotal = $billing->billingItems()
                                       ->where('item_type', 'professional')
                                       ->sum(DB::raw('quantity * unit_price'));
            
            $otherTotal = $billing->billingItems()
                                ->where('item_type', '!=', 'professional')
                                ->sum(DB::raw('quantity * unit_price'));
            
            $totalAmount = $professionalTotal + $otherTotal;
            
            // Update billing record
            $billing->update([
                'professional_fees' => $professionalTotal,
                'total_amount' => $totalAmount,
                'is_senior_citizen' => $request->boolean('is_senior_citizen'),
                'is_pwd' => $request->boolean('is_pwd'),
                'status' => $request->status,
                'notes' => $request->notes
            ]);
            
            // Recalculate deductions
            $philhealthDeduction = $billing->calculatePhilhealthDeduction();
            $seniorPwdDiscount = $billing->calculateSeniorPwdDiscount();
            $netAmount = $billing->calculateNetAmount();
            
            $billing->update([
                'philhealth_deduction' => $philhealthDeduction,
                'senior_pwd_discount' => $seniorPwdDiscount,
                'net_amount' => $netAmount
            ]);
            
            DB::commit();
            
            return redirect()->route('billings.show', $billing)
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
            
            return redirect()->route('billings.index')
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
        
        $pdf = Pdf::loadView('billings.receipt', compact('billing'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('billing-receipt-' . $billing->billing_number . '.pdf');
    }

    /**
     * Get patient services for billing
     */
    public function getPatientServices(Request $request)
    {
        \Log::info('Getting patient services for patient ID: ' . $request->patient_id);
        
        try {
            $patient = Patient::with(['labOrders', 'pharmacyRequests'])->findOrFail($request->patient_id);
            
            \Log::info('Patient found: ' . $patient->display_name);
            \Log::info('Admission diagnosis: ' . $patient->admission_diagnosis);
            \Log::info('Lab orders count: ' . $patient->labOrders->count());
            
            $billableServices = $patient->billable_services;
            \Log::info('Billable services count: ' . count($billableServices));
            
            if (count($billableServices) > 0) {
                \Log::info('First service: ' . json_encode($billableServices[0]));
            }
            
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
            \Log::error('Error getting patient services: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
