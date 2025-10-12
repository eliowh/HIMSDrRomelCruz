<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LabOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = LabOrder::with(['patient', 'requestedBy', 'labTech'])
            ->orderBy('requested_at', 'desc');
            
        // Apply status filter if provided
        $status = $request->input('status', 'all');
        if ($status && $status !== 'all' && in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }
        
        $orders = $query->paginate(10);

        // Calculate status counts for filter tabs
        $statusCounts = [
            'all' => LabOrder::count(),
            'pending' => LabOrder::where('status', 'pending')->count(),
            'in_progress' => LabOrder::where('status', 'in_progress')->count(),
            'completed' => LabOrder::where('status', 'completed')->count(),
            'cancelled' => LabOrder::where('status', 'cancelled')->count(),
        ];

        return view('labtech.labtech_orders', compact('orders', 'status', 'statusCounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'test_requested' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'required|in:normal,urgent,stat',
            'test_price' => 'nullable|numeric|min:0'
        ]);

        $patient = Patient::findOrFail($request->patient_id);

        $labOrder = LabOrder::create([
            'patient_id' => $patient->id,
            'admission_id' => $request->admission_id,
            'requested_by' => auth()->id(),
            'patient_name' => $patient->first_name . ' ' . $patient->last_name,
            'patient_no' => $patient->patient_no,
            'test_requested' => $request->test_requested,
            'notes' => $request->notes,
            'priority' => $request->priority,
            'price' => $request->test_price ?? 0.00,
            'requested_at' => Carbon::now(),
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lab order created successfully',
            'order_id' => $labOrder->id
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,in_progress,completed,cancelled',
                'results' => 'nullable|string',
                'results_pdf' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB PDF
                'cancel_reason' => 'required_if:status,cancelled|nullable|string|max:1000',
            ]);

            $order = LabOrder::findOrFail($id);
        
        $updateData = ['status' => $request->status];
        
        if ($request->status === 'in_progress' && !$order->started_at) {
            $updateData['started_at'] = Carbon::now();
            $updateData['lab_tech_id'] = auth()->id();
        }
        
        if ($request->status === 'completed') {
            $updateData['completed_at'] = Carbon::now();
            
            if ($request->results) {
                $updateData['results'] = $request->results;
            }
            
            // Handle PDF upload (optional)
            if ($request->hasFile('results_pdf')) {
                $file = $request->file('results_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('lab-results', $filename, 'local');
                $updateData['results_pdf_path'] = $path;
            }
            
            // Set lab tech who completed the order
            $updateData['lab_tech_id'] = auth()->id();
        }

        if ($request->status === 'cancelled') {
            $updateData['cancelled_at'] = Carbon::now();
            $updateData['lab_tech_id'] = auth()->id();
            
            // Save the cancellation reason
            if ($request->has('cancel_reason')) {
                $updateData['cancel_reason'] = $request->cancel_reason;
            }
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'pdf_uploaded' => $request->hasFile('results_pdf')
        ]);
        
        } catch (\Exception $e) {
            \Log::error('Error updating lab order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewOrder($id)
    {
        $order = LabOrder::with(['patient', 'requestedBy', 'labTech'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'order' => $order,
            'pdf_url' => $order->results_pdf_path ? route('labtech.order.viewPdf', $id) : null
        ]);
    }

    public function downloadPdf($id)
    {
        $order = LabOrder::findOrFail($id);
        
        if (!$order->results_pdf_path || !Storage::exists($order->results_pdf_path)) {
            return response()->json(['error' => 'PDF file not found'], 404);
        }
        
        return Storage::download($order->results_pdf_path, 
            'Lab_Results_' . $order->patient_name . '_' . $order->id . '.pdf');
    }
    
    /**
     * Get a patient's lab test history
     *
     * @param int $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatientTestHistory(Request $request, $patientId)
    {
        try {
            // Get all lab orders for the patient, ordered by latest first
            $query = LabOrder::where('patient_id', $patientId)
                ->with(['requestedBy', 'labTech', 'analyses.doctor']);
            
            // Filter by admission if admission_id is provided - STRICT filtering
            $admissionId = $request->query('admission_id');
            if ($admissionId) {
                // Only show lab orders that are explicitly linked to this admission
                $query->where('admission_id', '=', $admissionId);
            }
            
            $testHistory = $query->orderBy('requested_at', 'desc')->get();
            
            // Add price information and analysis data to each test
            $testHistoryWithPrices = $testHistory->map(function($test) {
                $testData = $test->toArray();
                
                // Use stored price if available, otherwise try to find from pricing tables
                $price = $test->price ?? $this->findTestPrice($test->test_requested);
                $testData['price'] = $price;
                $testData['procedure_price'] = $price;
                $testData['cost'] = $price;
                
                // Add analysis information
                $testData['has_analysis'] = $test->analyses->count() > 0;
                $testData['latest_analysis'] = null;
                
                if ($test->analyses->count() > 0) {
                    $latestAnalysis = $test->analyses->sortByDesc('created_at')->first();
                    $testData['latest_analysis'] = [
                        'id' => $latestAnalysis->id,
                        'clinical_notes' => $latestAnalysis->clinical_notes,
                        'recommendations' => $latestAnalysis->recommendations,
                        'doctor_name' => $latestAnalysis->doctor->name ?? 'Unknown Doctor',
                        'analyzed_at' => $latestAnalysis->created_at,
                        'has_analysis_pdf' => true
                    ];
                }
                
                return $testData;
            });
            
            return response()->json([
                'success' => true,
                'tests' => $testHistoryWithPrices
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching patient test history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving test history: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Find price for a test procedure from pricing tables
     *
     * @param string $testName
     * @return float
     */
    private function findTestPrice($testName)
    {
        if (!$testName) {
            return 0.0;
        }
        
        // List of pricing tables to search
        $pricingTables = [
            'laboratory_prices',
            'xray_prices', 
            'ultrasound_prices'
        ];
        
        foreach ($pricingTables as $table) {
            try {
                // Check if table exists
                if (!Schema::hasTable($table)) {
                    continue;
                }
                
                // Get table columns to determine the correct column names
                $columns = Schema::getColumnListing($table);
                
                // Find name and price columns
                $nameColumns = ['procedure_name', 'test_name', 'name', 'procedure', 'test'];
                $priceColumns = ['price', 'procedure_price', 'test_price', 'cost'];
                
                $nameColumn = null;
                $priceColumn = null;
                
                foreach ($nameColumns as $col) {
                    if (in_array($col, $columns)) {
                        $nameColumn = $col;
                        break;
                    }
                }
                
                foreach ($priceColumns as $col) {
                    if (in_array($col, $columns)) {
                        $priceColumn = $col;
                        break;
                    }
                }
                
                // If we found both columns, search for the test
                if ($nameColumn && $priceColumn) {
                    $result = DB::table($table)
                        ->where($nameColumn, 'LIKE', "%{$testName}%")
                        ->first();
                    
                    if ($result && isset($result->{$priceColumn})) {
                        return (float) $result->{$priceColumn};
                    }
                }
                
            } catch (\Exception $e) {
                \Log::warning("Error searching price in table {$table}: " . $e->getMessage());
                continue;
            }
        }
        
        return 0.0; // Default price if not found
    }

    /**
     * Check if PDF results exist for a lab order
     */
    public function checkPdf($orderId)
    {
        try {
            $order = LabOrder::findOrFail($orderId);
            
            $pdfExists = false;
            if ($order->results_pdf_path && Storage::exists($order->results_pdf_path)) {
                $pdfExists = true;
            }
            
            return response()->json([
                'success' => true,
                'pdf_exists' => $pdfExists,
                'pdf_path' => $order->results_pdf_path
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking PDF for order ' . $orderId . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking PDF availability'
            ], 500);
        }
    }

    /**
     * View PDF results for a lab order
     */
    public function viewPdf($orderId)
    {
        try {
            $order = LabOrder::findOrFail($orderId);
            
            if (!$order->results_pdf_path || !Storage::exists($order->results_pdf_path)) {
                abort(404, 'PDF results not found');
            }
            
            $pdfContent = Storage::get($order->results_pdf_path);
            
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="test_results_' . $orderId . '.pdf"');
                
        } catch (\Exception $e) {
            \Log::error('Error viewing PDF for order ' . $orderId . ': ' . $e->getMessage());
            abort(500, 'Error loading PDF results');
        }
    }

    /**
     * Return available lab result templates (for dynamic form selection)
     */
    public function listTemplates()
    {
        $templates = config('lab_templates');
        $public = collect($templates)->map(function($tpl, $key){
            return [
                'key' => $key,
                'title' => $tpl['title'],
                'code' => $tpl['code'] ?? strtoupper($key),
                'type' => isset($tpl['sections']) ? 'sectioned' : 'flat',
                'field_count' => isset($tpl['fields']) ? count($tpl['fields']) : collect($tpl['sections'] ?? [])->flatten(1)->count(),
            ];
        })->values();

        // Return full template structures if explicitly requested (for dynamic field rendering)
        if (request()->boolean('details')) {
            return response()->json([
                'success' => true,
                'templates' => $public,
                'templates_full' => $templates,
            ]);
        }

        return response()->json(['success' => true, 'templates' => $public]);
    }

    /**
     * Generate and attach a lab result PDF from a chosen template & submitted values
     */
    public function generateResultPdf(Request $request, $orderId)
    {
        try {
            // Debug check - let's see what's going on
            \Log::info('PDF generation started', [
                'order_id' => $orderId,
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role ?? 'no-role',
                'request_data' => $request->all()
            ]);

            // Validate user authentication (role is already checked by middleware)
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated.'
                ], 401);
            }

            $request->validate([
                'template_key' => 'required|string',
                'values' => 'array',
            ]);

            $order = LabOrder::with('patient')->findOrFail($orderId);

            if (!in_array($order->status, ['in_progress','pending'])) {
                return response()->json(['success'=>false,'message'=>'Order already completed or cancelled.'], 400);
            }

            $templates = config('lab_templates');
            $key = $request->input('template_key');
            if (!isset($templates[$key])) {
                return response()->json(['success'=>false,'message'=>'Unknown template key.'], 422);
            }
            $template = $templates[$key];
            $values = $request->input('values', []);

            // Build PDF view data
            $patient = $order->patient; // for blade

            if (!$patient) {
                return response()->json(['success'=>false,'message'=>'Patient not found for this order.'], 404);
            }

            // Get current user for signature
            $currentUser = auth()->user();

            // Try to get logo data safely
            $logoData = $this->getLogoSafely();

            // Prefer dedicated design-specific blade if exists (resources/views/labtech/templates/pdf/{key}.blade.php)
            $viewName = 'labtech.templates.lab_result_generic';
            if (view()->exists('labtech.templates.pdf.'.$key)) {
                $viewName = 'labtech.templates.pdf.'.$key;
            }
            
            \Log::info('Generating PDF with view: ' . $viewName, [
                'template_key' => $key,
                'order_id' => $orderId,
                'patient_id' => $patient->id ?? 'null'
            ]);

            $pdf = Pdf::loadView($viewName, compact('template','values','patient','currentUser','logoData'));
            $pdf->setPaper('letter','portrait');

            $filename = 'lab-result-'.$order->id.'-'.$key.'-'.time().'.pdf';
            $path = 'lab-results/'.$filename;
            Storage::put($path, $pdf->output());

            $order->results_pdf_path = $path;
            $order->results = ($template['title'] ?? 'Lab Result').' generated';
            $order->status = 'completed';
            $order->completed_at = now();
            $order->lab_tech_id = auth()->id();
            $order->save();

            return response()->json([
                'success'=>true,
                'message'=>'Lab result PDF generated and attached.',
                'pdf_path'=>$path,
                'view_url'=> route('labtech.order.viewPdf', $order->id),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in PDF generation', [
                'order_id' => $orderId,
                'errors' => $e->validator->errors()->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Lab PDF generation failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Safely get logo data for PDF generation
     */
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

    /**
     * Show lab requests for doctors (with doctor-specific status logic)
     */
    public function doctorResults(Request $request)
    {
        // Get status filter - default to 'pending_analysis'
        $status = $request->input('status', 'pending_analysis');
        
        $query = LabOrder::with(['patient', 'requestedBy', 'labTech', 'analyses' => function($q) {
                $q->where('doctor_id', auth()->id());
            }]);
            
        // Filter by doctor-specific status
        if ($status === 'pending') {
            // Lab orders that haven't been completed by lab yet
            $query->whereIn('status', ['pending', 'in_progress']);
        } elseif ($status === 'pending_analysis') {
            // Lab completed but doctor hasn't analyzed yet
            $query->where('status', 'completed')
                  ->whereNotNull('results_pdf_path')
                  ->whereDoesntHave('analyses', function($q) {
                      $q->where('doctor_id', auth()->id());
                  });
        } elseif ($status === 'completed') {
            // Doctor has completed analysis
            $query->where('status', 'completed')
                  ->whereNotNull('results_pdf_path')
                  ->whereHas('analyses', function($q) {
                      $q->where('doctor_id', auth()->id());
                  });
        }
        
        $query->orderBy($status === 'completed' ? 'completed_at' : 'requested_at', 'desc');
            
        // Search functionality
        $search = $request->input('search', '');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($patientQuery) use ($search) {
                    $patientQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('patient_no', 'like', "%{$search}%");
                })
                ->orWhere('test_requested', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        $results = $query->paginate(15);
        
        // Calculate status counts for tabs (doctor-specific)
        $statusCounts = [
            'pending' => LabOrder::whereIn('status', ['pending', 'in_progress'])->count(),
            'pending_analysis' => LabOrder::where('status', 'completed')
                ->whereNotNull('results_pdf_path')
                ->whereDoesntHave('analyses', function($q) {
                    $q->where('doctor_id', auth()->id());
                })->count(),
            'completed' => LabOrder::where('status', 'completed')
                ->whereNotNull('results_pdf_path')
                ->whereHas('analyses', function($q) {
                    $q->where('doctor_id', auth()->id());
                })->count(),
        ];

        return view('doctor.doctor_results', compact('results', 'search', 'status', 'statusCounts'));
    }

    /**
     * Generate doctor's analysis PDF report
     */
    public function generateAnalysisPdf($labOrderId)
    {
        try {
            // Get the lab order with analysis
            // Don't filter by doctor_id for nurses - they should see all analyses
            $labOrder = LabOrder::with(['patient', 'labTech', 'analyses.doctor'])->findOrFail($labOrderId);
            
            // Get the latest analysis (regardless of who created it)
            $analysis = $labOrder->analyses->sortByDesc('created_at')->first();
            if (!$analysis) {
                return response()->json(['success' => false, 'message' => 'No analysis found for this lab order'], 404);
            }

            // Prepare data for PDF
            $data = [
                'labOrder' => $labOrder,
                'patient' => $labOrder->patient,
                'analysis' => $analysis,
                'doctor' => $analysis->doctor, // Use the doctor who created the analysis
                'currentDate' => now()->format('F j, Y'),
                'logoData' => $this->getLogoSafely()
            ];

            // Generate PDF
            $pdf = Pdf::loadView('doctor.templates.analysis_report', $data);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->stream("Analysis_Report_Order_{$labOrderId}.pdf");

        } catch (\Exception $e) {
            \Log::error('Analysis PDF generation failed', [
                'lab_order_id' => $labOrderId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save doctor's analysis for a lab result
     */
    public function saveAnalysis(Request $request)
    {
        $request->validate([
            'lab_order_id' => 'required|exists:lab_orders,id',
            'clinical_notes' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000'
        ]);

        // Check if analysis already exists for this lab order and doctor
        $analysis = \App\Models\LabAnalysis::where('lab_order_id', $request->lab_order_id)
                                          ->where('doctor_id', auth()->id())
                                          ->first();

        if ($analysis) {
            // Update existing analysis
            $analysis->update([
                'clinical_notes' => $request->clinical_notes,
                'recommendations' => $request->recommendations,
                'analyzed_at' => now()
            ]);
        } else {
            // Create new analysis
            \App\Models\LabAnalysis::create([
                'lab_order_id' => $request->lab_order_id,
                'doctor_id' => auth()->id(),
                'clinical_notes' => $request->clinical_notes,
                'recommendations' => $request->recommendations,
                'analyzed_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Analysis saved successfully'
        ]);
    }
}
