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
            'test_requested' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'required|in:normal,urgent,stat',
            'test_price' => 'nullable|numeric|min:0'
        ]);

        $patient = Patient::findOrFail($request->patient_id);

        $labOrder = LabOrder::create([
            'patient_id' => $patient->id,
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
    public function getPatientTestHistory($patientId)
    {
        try {
            // Get all lab orders for the patient, ordered by latest first
            $testHistory = LabOrder::where('patient_id', $patientId)
                ->with(['requestedBy', 'labTech'])
                ->orderBy('requested_at', 'desc')
                ->get();
            
            // Add price information to each test (use stored price or fallback to lookup)
            $testHistoryWithPrices = $testHistory->map(function($test) {
                $testData = $test->toArray();
                
                // Use stored price if available, otherwise try to find from pricing tables
                $price = $test->price ?? $this->findTestPrice($test->test_requested);
                $testData['price'] = $price;
                $testData['procedure_price'] = $price;
                $testData['cost'] = $price;
                
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

            // Validate user has lab_technician role
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated.'
                ], 401);
            }

            if (!auth()->user()->hasRole('lab_technician')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Lab technician access required. Your role: ' . (auth()->user()->role ?? 'none')
                ], 403);
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

            $pdf = Pdf::loadView($viewName, compact('template','values','patient'));
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
}
