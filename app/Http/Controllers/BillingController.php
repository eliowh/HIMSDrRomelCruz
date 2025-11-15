<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Patient;
use App\Models\Admission;
use App\Models\PhilhealthMember;
use App\Models\Icd10NamePriceRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Report;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = Billing::with(['patient', 'createdBy']);
        
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
        
        // Compute summary statistics (use clones to avoid modifying the base query)
        $totalBillings = (clone $query)->count();
        $paidBillsCount = (clone $query)->where('status', 'paid')->count();
        $pendingBillsCount = (clone $query)->where('status', 'pending')->count();
        // PhilHealth members: count distinct patients who have billings matching the current filter and flagged as philhealth
        $philhealthMembersCount = (clone $query)
                                    ->where('is_philhealth_member', true)
                                    ->distinct()
                                    ->count('patient_id');

        $billings = $query->orderBy('created_at', 'desc')->paginate(20);

        // Append search parameters to pagination links
        $billings->appends($request->only(['search', 'status']));

        return view('billing.index', compact('billings', 'totalBillings', 'paidBillsCount', 'pendingBillsCount', 'philhealthMembersCount'));
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
            'admission_id' => 'required|exists:admissions,id',
            'billing_items' => 'required|array|min:1',
            'billing_items.*.item_type' => 'required|in:room,medicine,laboratory,professional,other',
            'billing_items.*.description' => 'required|string',
            'billing_items.*.quantity' => 'required|numeric|min:0.01',
            'billing_items.*.unit_price' => 'required|numeric|min:0',
            'billing_items.*.case_rate' => 'nullable|numeric|min:0',
            'billing_items.*.icd_code' => 'nullable|string'
        ]);

        // Start a DB transaction and take a pessimistic lock on the admission row
        // to prevent concurrent requests from racing and creating duplicate billings
        // for the same admission. We re-check for existing billings while the
        // lock is held to make the operation atomic.
        DB::beginTransaction();

        try {
            // If admission_id is present, lock the admission row first to serialize
            // concurrent attempts to create a billing for the same admission.
            if ($request->admission_id) {
                $admission = Admission::where('id', $request->admission_id)->lockForUpdate()->first();
                if (!$admission) {
                    DB::rollback();
                    return back()->withErrors([
                        'admission' => 'Admission not found.'
                    ])->withInput();
                }

                // Re-check for any existing billing for this admission while the lock
                // is held. If found, abort the transaction and return an error.
                $existingBilling = Billing::where('admission_id', $request->admission_id)->first();
                if ($existingBilling) {
                    DB::rollback();
                    return back()->withErrors([
                        'admission' => 'A billing record already exists for this admission (Billing #' . $existingBilling->billing_number . '). Please edit the existing billing instead of creating a new one.'
                    ])->withInput();
                }
            }

            $patient = Patient::findOrFail($request->patient_id);
            
            // Check PhilHealth membership - prioritize user input over automatic lookup
            $isPhilhealthMember = $request->boolean('is_philhealth_member');

            // If checkbox is not checked, fall back to automatic lookup
            if (!$isPhilhealthMember) {
                $philhealthMember = PhilhealthMember::findByPatient($patient);
                $isPhilhealthMember = $philhealthMember && $philhealthMember->isEligibleForCoverage();
            }

            // Server-side enforcement: if any previous billing for this patient used PhilHealth,
            // force the flag to true to prevent accidental or malicious unchecking from the client.
            $hadPreviousPhilhealth = Billing::where('patient_id', $patient->id)
                                            ->where('is_philhealth_member', true)
                                            ->exists();
            if ($hadPreviousPhilhealth) {
                $isPhilhealthMember = true;
            }
            
            // Calculate totals
            $totalAmount = 0;
            $roomCharges = 0;
            $professionalFees = 0;
            $medicineCharges = 0;
            $labCharges = 0;
            $otherCharges = 0;
            
            foreach ($request->billing_items as $item) {
                // For professional items, both PhilHealth and non-PhilHealth members are charged Case Rate + Professional Fee
                // The difference is that PhilHealth members get a deduction that covers both
                if ($item['item_type'] === 'professional') {
                    $caseRate = (float)($item['case_rate'] ?? 0);
                    $professionalFee = (float)($item['unit_price'] ?? 0);
                    
                    // Both PhilHealth and non-PhilHealth: Case Rate + Professional Fee included in subtotal
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
            
            // Create billing record with a temporary unique placeholder for billing_number. We will
            // update the final billing_number after obtaining the auto-increment ID. This avoids
            // generating possibly-duplicate numbers when multiple requests occur concurrently.
            $billing = Billing::create([
                'patient_id' => $patient->id,
                'admission_id' => $request->admission_id,
                'billing_number' => 'TEMP-' . Str::uuid(),
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

            // Generate a stable, unique billing number based on the newly-created record's ID.
            // Using the record ID guarantees uniqueness and avoids race conditions when multiple
            // billings are created concurrently.
            $finalBillingNumber = 'BILL-' . date('Y') . '-' . str_pad($billing->id, 6, '0', STR_PAD_LEFT);
            $billing->billing_number = $finalBillingNumber;
            $billing->save();
            
            // Create billing items
            foreach ($request->billing_items as $item) {
                // Calculate correct total amount - both member types are charged Case Rate + Professional Fee
                if ($item['item_type'] === 'professional') {
                    $caseRate = (float)($item['case_rate'] ?? 0);
                    $professionalFee = (float)($item['unit_price'] ?? 0);
                    
                    // Both PhilHealth and non-PhilHealth: Case Rate + Professional Fee included in total
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
            // PhilHealth deduction is based on sum of Case Rate + Professional Fee for professional items when member checked
            $philhealthDeduction = 0;
            if ($billing->is_philhealth_member) {
                foreach ($billing->billingItems as $bi) {
                    if ($bi->item_type === 'professional') {
                        $quantity = $bi->quantity ?: 1;
                        $caseRate = $bi->case_rate ?: 0;
                        $professionalFee = $bi->unit_price ?: 0;
                        
                        // PhilHealth covers Case Rate + Professional Fee
                        $philhealthDeduction += (($caseRate + $professionalFee) * $quantity);
                    }
                }
            }

            $seniorPwdDiscount = $billing->calculateSeniorPwdDiscount();
            $netAmount = $billing->total_amount - $philhealthDeduction - $seniorPwdDiscount;
            
            // Update billing with calculations
            $billing->update([
                'philhealth_deduction' => $philhealthDeduction,
                'senior_pwd_discount' => $seniorPwdDiscount,
                'net_amount' => $netAmount
            ]);
            
            DB::commit();

            // Audit: Log billing creation
            try {
                Report::log('Billing Created', Report::TYPE_USER_REPORT, 'New billing created', [
                    'billing_id' => $billing->id,
                    'billing_number' => $billing->billing_number,
                    'patient_id' => $billing->patient_id,
                    'total_amount' => floatval($billing->total_amount ?? 0),
                    'net_amount' => floatval($billing->net_amount ?? 0),
                    'created_by' => $billing->created_by ?? auth()->id(),
                    'created_at' => now()->toDateTimeString(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create billing audit: ' . $e->getMessage());
            }

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
        // Prevent editing paid billings for security
        if ($billing->status === 'paid') {
            return redirect()->route('billing.show', $billing)
                           ->with('error', 'Paid billings cannot be edited. Contact administration if changes are needed.');
        }
        
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
        // Prevent updating paid billings for security
        if ($billing->status === 'paid') {
            return redirect()->route('billing.show', $billing)
                           ->with('error', 'Paid billings cannot be modified. Contact administration if changes are needed.');
        }
        
        try {
            $request->validate([
                'admission_id' => 'nullable|exists:admissions,id',
                'professional_fees' => 'nullable|numeric|min:0|max:999999.99',
                'is_philhealth_member' => 'boolean',
                'is_senior_citizen' => 'boolean',
                'is_pwd' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Check if admission_id is being changed and if the new admission already has a billing
            if ($request->admission_id && $request->admission_id != $billing->admission_id) {
                $existingBilling = Billing::where('admission_id', $request->admission_id)
                                         ->where('id', '!=', $billing->id)
                                         ->first();
                if ($existingBilling) {
                    return back()->withErrors([
                        'admission' => 'The selected admission already has a billing record (Billing #' . $existingBilling->billing_number . '). Cannot reassign billing to this admission.'
                    ])->withInput();
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors(['error' => 'Professional fee adjustment failed: ' . implode(' ', $e->validator->errors()->all())])
                        ->withInput();
        }

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
                        $quantity = $item->quantity ?: 1;
                        // total_amount should represent billed amount (case rate + professional fee) * qty
                        $newTotalAmount = ($caseRate + $newProfessionalFee) * $quantity;

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

            // Derive senior/pwd selection. Prefer the explicit radio `discount_type` when present
            // because the UI shows a mutually-exclusive choice. If the radio is missing (legacy),
            // fall back to the hidden boolean fields for compatibility.
            $discountType = $request->get('discount_type', null);
            if ($discountType !== null) {
                $isSeniorRequested = ($discountType === 'senior');
                $isPwdRequested = ($discountType === 'pwd');
            } else {
                $isSeniorRequested = $request->boolean('is_senior_citizen');
                $isPwdRequested = $request->boolean('is_pwd');
            }

            $tempBilling->is_senior_citizen = $isSeniorRequested;
            $tempBilling->is_pwd = $isPwdRequested;
            
            // Determine final philhealth flag with server-side enforcement
            $requestedPhilhealth = $request->boolean('is_philhealth_member');
            $hasPreviousPhilhealth = Billing::where('patient_id', $billing->patient_id)
                                            ->where('is_philhealth_member', true)
                                            ->where('id', '!=', $billing->id)
                                            ->exists();

            if ($hasPreviousPhilhealth) {
                $finalIsPhilhealth = true;
            } else {
                $finalIsPhilhealth = $requestedPhilhealth;
            }

            $tempBilling->is_philhealth_member = $finalIsPhilhealth;

            // PhilHealth deduction based on Case Rate + Professional Fee when checked
            $philhealthDeduction = 0;
            if ($tempBilling->is_philhealth_member) {
                foreach ($billing->billingItems as $bi) {
                    if ($bi->item_type === 'professional') {
                        $quantity = $bi->quantity ?: 1;
                        $caseRate = $bi->case_rate ?: 0;
                        $professionalFee = $bi->unit_price ?: 0;
                        
                        // PhilHealth covers Case Rate + Professional Fee
                        $philhealthDeduction += (($caseRate + $professionalFee) * $quantity);
                    }
                }
            }

            $seniorPwdDiscount = $tempBilling->calculateSeniorPwdDiscount();
            $netAmount = $totalAmount - $philhealthDeduction - $seniorPwdDiscount;
            
            // Build update payload. Only change admission_id when the request explicitly
            // provides it to avoid accidentally unassigning the billing from its admission
            // (which allowed creating a duplicate billing for the same admission).
            $updatePayload = [
                'room_charges' => $roomCharges,
                'professional_fees' => $professionalTotal,
                'medicine_charges' => $medicineCharges,
                'lab_charges' => $labCharges,
                'other_charges' => $otherCharges,
                'total_amount' => $totalAmount,
                'philhealth_deduction' => $philhealthDeduction,
                'senior_pwd_discount' => $seniorPwdDiscount,
                'net_amount' => $netAmount,
                'is_philhealth_member' => $finalIsPhilhealth,
                // Persist boolean flags; prefer hidden inputs but fall back to discount_type radio
                'is_senior_citizen' => $isSeniorRequested,
                'is_pwd' => $isPwdRequested,
                // status updates are managed via payment flow and not editable here
                'notes' => $request->notes
            ];

            if ($request->filled('admission_id')) {
                $updatePayload['admission_id'] = $request->admission_id;
            }

            // Update billing record with all calculated values
            $billing->update($updatePayload);
            
            DB::commit();
            
            return redirect()->route('billing.show', $billing)
                           ->with('success', 'Billing updated successfully.');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update billing: ' . $e->getMessage()]);
        }
    }

    // Delete functionality removed for security - preventing billing theft and data loss

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

    /**
     * Return the last known PhilHealth checkbox status from existing billings for this patient.
     * If the patient has any previous billing with is_philhealth_member = true, return that fact so
     * the frontend can auto-check/lock the checkbox to avoid accidental unchecking.
     */
    public function lastPhilhealthStatus(Request $request)
    {
        $patientId = $request->input('patient_id');
        if (!$patientId) {
            return response()->json(['error' => 'patient_id is required'], 400);
        }

        $lastBilling = Billing::where('patient_id', $patientId)
                              ->orderBy('created_at', 'desc')
                              ->first();

        return response()->json([
            'has_previous_billing' => (bool) $lastBilling,
            'last_is_philhealth_member' => $lastBilling ? (bool) $lastBilling->is_philhealth_member : false,
            'philhealth_deduction' => $lastBilling ? ($lastBilling->philhealth_deduction ?? 0) : 0
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
        $logoData = $this->getLogoSafely();
        // Use the compact fragment wrapper for PDF generation so exported PDFs are
        // compact and consistent with the cashier/billing print fragment.
        $isPdf = true;
        $isBilling = true;
        $pdf = Pdf::loadView('billing.receipt_wrapper', compact('billing', 'logoData', 'isPdf', 'isBilling'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('billing-receipt-' . $billing->billing_number . '.pdf');
    }

    public function viewReceipt(Billing $billing)
    {
        $billing->load(['patient', 'billingItems', 'createdBy']);
        $logoData = $this->getLogoSafely();
        $autoPrint = true; // Flag to auto-trigger print dialog
        // Return a minimal wrapper that includes the fragment so the billing role
        // sees the compact receipt and the print dialog is consistent with PDF output.
        $isBilling = true;
        return view('billing.receipt_wrapper', compact('billing', 'logoData', 'autoPrint', 'isBilling'));
    }

    /**
     * Get patient services for billing
     */
    public function getPatientServices(Request $request)
    {
        try {
            $patientId = $request->patient_id;
            $admissionId = $request->query('admission_id');
            

            
            // Basic error checking first
            if (!$patientId) {
                return response()->json(['error' => 'Patient ID is required'], 400);
            }
            
            $patient = Patient::findOrFail($patientId);
            
            // Require admission_id for proper isolation
            if (!$admissionId) {
                return response()->json(['error' => 'Admission ID is required for service loading'], 400);
            }
            
            // Filter services by admission
                // Get admission details
                $admission = \DB::table('admissions')->where('id', $admissionId)->first();
                
                if (!$admission) {
                    return response()->json(['error' => 'Admission not found'], 404);
                }
                

                
                $admissionStart = $admission->admission_date;
                $admissionEnd = $admission->discharge_date ?? now();
                
                // Get room service from admission with dynamic pricing
                $roomPrice = 2400; // Default price
                try {
                    if ($admission->room_no) {
                        $room = \DB::table('roomlist')->where('COL 1', $admission->room_no)->first();
                        if ($room && isset($room->{'COL 2'})) {
                            // Clean room price (remove commas if present)
                            $cleanPrice = is_string($room->{'COL 2'}) ? str_replace(',', '', $room->{'COL 2'}) : $room->{'COL 2'};
                            $roomPrice = (float) $cleanPrice;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Room price lookup failed', ['error' => $e->getMessage(), 'room_no' => $admission->room_no]);
                    // Continue with default price
                }
                
                $roomService = [
                    'type' => 'room',
                    'description' => $admission->room_no ?: 'Room',
                    'source' => 'room',
                    'quantity' => 1,
                    'unit_price' => $roomPrice,
                ];
                
                // Get ICD-10 service from admission if available. Prefer final_diagnosis when present
                // (doctor-finalized). If missing, fall back to the initial admission_diagnosis set by the nurse.
                $icdServices = [];
                try {
                    $diagnosis = $admission->final_diagnosis ?: $admission->admission_diagnosis;

                    if ($diagnosis) {
                        // Try to get ICD rates from database first
                        $icdRate = \DB::table('icd10namepricerate')
                            ->where('COL 1', $diagnosis)
                            ->first();

                        if ($icdRate) {
                            $icdServices[] = [
                                'type' => 'professional',
                                'description' => $icdRate->{'COL 2'} ?? $diagnosis, // Description from COL 2
                                'icd_code' => $diagnosis,
                                'source' => 'admission',
                                'quantity' => 1,
                                'case_rate' => $icdRate->{'COL 3'} ?? 2340, // Case rate from COL 3
                                'unit_price' => $icdRate->{'COL 4'} ?? 7800, // Professional fee from COL 4
                            ];
                        } else {
                            // Fallback with corrected values (case_rate should be lower, professional_fee higher)
                            $icdServices[] = [
                                'type' => 'professional',
                                'description' => $diagnosis,
                                'icd_code' => $diagnosis,
                                'source' => 'admission',
                                'quantity' => 1,
                                'case_rate' => 2340, // Case rate (lower amount)
                                'unit_price' => 7800, // Professional fee (higher amount)
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('ICD service lookup failed', ['error' => $e->getMessage(), 'diagnosis' => ($diagnosis ?? $admission->admission_diagnosis)]);
                    // Continue without ICD services
                }
                
                // Get lab orders for this admission - STRICTLY only this admission
                $labOrders = \DB::table('lab_orders')
                    ->where('patient_id', $patientId)
                    ->where('admission_id', '=', $admissionId) // Exact match only
                    ->get();
                

                
                $labServices = $labOrders->map(function($lab) {
                    return [
                        'type' => 'laboratory',
                        'description' => $lab->test_requested,
                        'source' => 'lab_order',
                        'quantity' => 1,
                        'unit_price' => $lab->price ?? 360, // Default lab price
                    ];
                });
                
                // Get medicines for this admission - STRICTLY only this admission
                $medicines = \DB::table('patient_medicines as pm')
                    ->join('pharmacy_requests as pr', 'pm.pharmacy_request_id', '=', 'pr.id')
                    ->where('pm.patient_id', $patientId)
                    ->where('pr.admission_id', '=', $admissionId) // Exact match only
                    ->whereNotNull('pr.admission_id') // Must have admission_id (exclude NULL values)
                    ->select('pm.*')
                    ->get();
                

                
                $medicineServices = $medicines->map(function($medicine) {
                    return [
                        'type' => 'medicine',
                        'description' => $medicine->generic_name ?: $medicine->brand_name,
                        'source' => 'patient_medicine',
                        'quantity' => $medicine->quantity,
                        'unit_price' => $medicine->unit_price,
                    ];
                });
                
            // Combine all services
            $billableServices = collect([$roomService])
                ->merge($icdServices)
                ->merge($labServices)
                ->merge($medicineServices)
                ->values()
                ->toArray();
                

            
            return response()->json([
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->display_name,
                    'patient_no' => $patient->patient_no,
                    // Return the effective diagnosis used for billing (final_diagnosis if present)
                    'admission_diagnosis' => ($diagnosis ?? $patient->admission_diagnosis)
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
                      ->with(['admissions.billings'])
                      ->limit(20) // Increased limit to account for filtering
                      ->get()
                      ->filter(function ($patient) {
                          // Include patient if they have no admissions
                          if ($patient->admissions->isEmpty()) {
                              return true;
                          }

                          // Include patient if they have at least one admission that does NOT have an active (pending/paid) billing.
                          // This prevents offering patients for new billing when an admission already has an active billing.
                          foreach ($patient->admissions as $admission) {
                              // Consider a billing 'active' if its status is 'pending' or 'paid'. 'cancelled' billings do not block new billings.
                                                        // Admission is eligible only if it has NO billings at all. We block creation
                                                        // whenever a billing record exists for the admission to avoid duplicates.
                                                        $hasAnyBilling = $admission->billings->isNotEmpty();
                                                            if (!$hasAnyBilling) {
                                                                    return true;
                                                            }
                          }

                          // Exclude patient if all admissions have an active billing (pending or paid)
                          return false;
                      })
                      ->take(10) // Final limit after filtering
                      ->map(function ($patient) {
                          return [
                              'id' => $patient->id,
                              'text' => $patient->display_name . ' (Patient #: ' . $patient->patient_no . ')'
                          ];
                      })
                      ->values(); // Reindex array

        return response()->json(['patients' => $patients]);
    }

    /**
     * Return recent patients for dropdown when search field is focused with empty query
     */
    public function recentPatients(Request $request)
    {
        // Return most recently updated patients but apply the same admission/billing filters
        $patients = Patient::with(['admissions.billings'])
                        ->orderBy('updated_at', 'desc')
                        ->limit(40)
                        ->get()
                        ->filter(function ($patient) {
                            // Include patient if they have no admissions
                            if ($patient->admissions->isEmpty()) {
                                return true;
                            }

                            // Include patient if they have at least one admission that does NOT have an active (pending/paid) billing.
                            foreach ($patient->admissions as $admission) {
                                $hasAnyBilling = $admission->billings->isNotEmpty();
                                if (!$hasAnyBilling) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->take(10)
                        ->map(function ($patient) {
                            return [
                                'id' => $patient->id,
                                'text' => $patient->display_name . ' (Patient #: ' . $patient->patient_no . ')'
                            ];
                        })
                        ->values();

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
                'message' => 'Billing marked as paid successfully. Patient clearance provided.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark billing as paid: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get patient admissions for billing
     */
    public function getPatientAdmissions(Request $request, $patientId)
    {
        try {
            $patient = Patient::findOrFail($patientId);
            
            $admissions = $patient->admissions()
                                ->orderBy('admission_date', 'desc')
                                ->get()
                                ->map(function ($admission) {
                                    return [
                                        'id' => $admission->id,
                                        'admission_number' => $admission->admission_number,
                                        'doctor_name' => $admission->doctor_name,
                                        'admission_date' => $admission->admission_date,
                                        'status' => $admission->status,
                                        'diagnosis' => $admission->admission_diagnosis
                                    ];
                                });

            return response()->json(['admissions' => $admissions]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch patient admissions: ' . $e->getMessage()], 500);
        }
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
