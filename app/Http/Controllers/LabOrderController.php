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
     * Generate and download a lab result PDF with proper formatting
     */
    public function generateLabResultPDF($order_id)
    {
        $order = LabOrder::with(['patient', 'requestedBy'])->findOrFail($order_id);
        
        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'Order is not completed yet.');
        }
        
        $testType = null;
        if (str_contains(strtolower($order->test_requested), 'cbc')) {
            $testType = 'cbc';
        } elseif (str_contains(strtolower($order->test_requested), 'chemistry')) {
            $testType = 'chemistry';
        } elseif (str_contains(strtolower($order->test_requested), 'urinalysis')) {
            $testType = 'urinalysis';
        }
        
        // Collect results based on test type
        $resultsData = [];
        if ($testType === 'cbc') {
            $resultsData = $this->collectCBCResults($order);
        } elseif ($testType === 'chemistry') {
            $resultsData = $this->collectChemistryResults($order);
        } elseif ($testType === 'urinalysis') {
            $resultsData = $this->collectUrinalysisResults($order);
        } else {
            $resultsData = [
                'general_results' => $order->results,
                'interpretation' => $order->interpretation,
                'notes' => $order->notes,
                'completed_by' => $order->completed_by ?? 'Lab Technician'
            ];
        }
        
        $html = $this->generatePDFContent($order, $resultsData, $testType);
        $css = $this->getPDFStyles();
        
        $pdf = new Dompdf();
        $pdf->loadHtml($css . $html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        
        $filename = 'lab_result_' . $order->patient_name . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Get CSS styles for PDF
     */
    private function getPDFStyles()
    {
        return "
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #333;
                padding: 20px;
            }
            
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .header h1 {
                font-size: 24px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 5px;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            
            .header h2 {
                font-size: 16px;
                color: #34495e;
                margin-bottom: 10px;
                font-weight: normal;
            }
            
            .patient-info {
                margin-bottom: 25px;
            }
            
            .patient-info table {
                font-size: 11px;
            }
            
            .results-section {
                margin-bottom: 25px;
            }
            
            .results-section h3 {
                font-size: 14px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 15px;
                text-align: center;
                padding: 8px;
                background: #ecf0f1;
                border: 2px solid #bdc3c7;
            }
            
            .interpretation-section h3,
            .notes-section h3 {
                font-size: 12px;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 10px;
                margin-top: 20px;
            }
            
            table {
                font-size: 10px;
                line-height: 1.3;
            }
            
            table th {
                font-weight: bold;
                font-size: 9px;
                padding: 8px 6px !important;
            }
            
            table td {
                padding: 6px 8px !important;
                font-size: 10px;
            }
            
            .footer {
                margin-top: 40px;
                border-top: 1px solid #bdc3c7;
                padding-top: 15px;
            }
            
            @page {
                margin: 1.5cm;
            }
        </style>";
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
     * Get single lab order details
     */
    public function show($id)
    {
        try {
            $order = LabOrder::with(['patient', 'requestedBy', 'labTech'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching order details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    /**
     * Complete lab order with structured results and generate PDF
     */
    public function completeWithPdf(Request $request, $id)
    {
        try {
            $order = LabOrder::with(['patient'])->findOrFail($id);
            
            // Validate the request data
            $validatedData = $request->validate([
                'test_type' => 'required|string',
                'interpretation' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            // Collect test results based on test type
            $testType = $request->input('test_type');
            $resultsData = [];
            
            if ($testType === 'cbc') {
                $resultsData = $this->collectCBCResults($request);
            } elseif ($testType === 'chemistry') {
                $resultsData = $this->collectChemistryResults($request);
            } elseif ($testType === 'urinalysis') {
                $resultsData = $this->collectUrinalysisResults($request);
            } else {
                $resultsData = [
                    'general_results' => $request->input('general_results', '')
                ];
            }

            // Add common fields
            $resultsData['test_type'] = $testType;
            $resultsData['interpretation'] = $request->input('interpretation', '');
            $resultsData['notes'] = $request->input('notes', '');
            $resultsData['completed_by'] = auth()->user()->name;
            $resultsData['completed_at'] = now()->format('Y-m-d H:i:s');

            // Generate PDF
            $pdfPath = $this->generateResultsPDF($order, $resultsData);

            // Update the order
            $order->update([
                'status' => 'completed',
                'results' => json_encode($resultsData),
                'results_pdf_path' => $pdfPath,
                'completed_at' => now(),
                'lab_tech_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lab order completed and PDF generated successfully',
                'pdf_path' => $pdfPath
            ]);

        } catch (\Exception $e) {
            \Log::error('Error completing order with PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Collect CBC test results from request
     */
    private function collectCBCResults(Request $request)
    {
        return [
            'rbc_count' => $request->input('rbc_count'),
            'hemoglobin' => $request->input('hemoglobin'),
            'hematocrit' => $request->input('hematocrit'),
            'platelet_count' => $request->input('platelet_count'),
            'wbc_count' => $request->input('wbc_count'),
            'mcv' => $request->input('mcv'),
            'mch' => $request->input('mch'),
            'mchc' => $request->input('mchc'),
            'neutrophils' => $request->input('neutrophils'),
            'lymphocytes' => $request->input('lymphocytes'),
            'monocytes' => $request->input('monocytes'),
            'eosinophils' => $request->input('eosinophils'),
            'basophils' => $request->input('basophils')
        ];
    }

    /**
     * Collect Chemistry test results from request
     */
    private function collectChemistryResults(Request $request)
    {
        return [
            'fbs' => $request->input('fbs'),
            'rbs' => $request->input('rbs'),
            'hba1c' => $request->input('hba1c'),
            'total_cholesterol' => $request->input('total_cholesterol'),
            'hdl' => $request->input('hdl'),
            'ldl' => $request->input('ldl'),
            'triglycerides' => $request->input('triglycerides'),
            'alt' => $request->input('alt'),
            'ast' => $request->input('ast'),
            'total_bilirubin' => $request->input('total_bilirubin'),
            'direct_bilirubin' => $request->input('direct_bilirubin'),
            'bun' => $request->input('bun'),
            'creatinine' => $request->input('creatinine'),
            'uric_acid' => $request->input('uric_acid')
        ];
    }

    /**
     * Collect Urinalysis test results from request
     */
    private function collectUrinalysisResults(Request $request)
    {
        return [
            'urine_color' => $request->input('urine_color'),
            'urine_clarity' => $request->input('urine_clarity'),
            'specific_gravity' => $request->input('specific_gravity'),
            'urine_ph' => $request->input('urine_ph'),
            'protein' => $request->input('protein'),
            'glucose' => $request->input('glucose'),
            'ketones' => $request->input('ketones'),
            'urine_rbc' => $request->input('urine_rbc'),
            'urine_wbc' => $request->input('urine_wbc'),
            'epithelial_cells' => $request->input('epithelial_cells'),
            'bacteria' => $request->input('bacteria'),
            'casts' => $request->input('casts'),
            'crystals' => $request->input('crystals')
        ];
    }

    /**
     * Generate PDF for lab results
     */
    private function generateResultsPDF($order, $resultsData)
    {
        // Create PDF content based on test type
        $testType = $resultsData['test_type'];
        $html = $this->generatePDFContent($order, $resultsData, $testType);
        
        // Generate filename
        $filename = 'lab_results_' . $order->id . '_' . time() . '.pdf';
        $pdfPath = 'lab_results/' . $filename;
        
        // For now, create a simple HTML-to-PDF conversion
        // In a real application, you'd use a PDF library like DomPDF
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Lab Results - {$order->patient_name}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                .patient-info { background: #f5f5f5; padding: 10px; margin-bottom: 20px; }
                .results { margin-bottom: 20px; }
                .test-panel { margin-bottom: 15px; }
                .test-title { font-weight: bold; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
                .test-row { padding: 5px 0; }
                .reference { color: #666; font-size: 0.9em; }
            </style>
        </head>
        <body>
            {$html}
        </body>
        </html>";
        
        // Save as HTML file (you can later convert to PDF using a proper library)
        Storage::put($pdfPath, $htmlContent);
        
        return $pdfPath;
    }

    /**
     * Generate HTML content for PDF based on test type
     */
    private function generatePDFContent($order, $resultsData, $testType)
    {
        $html = "
        <div class='header'>
            <h1>ROMEL CRUZ HOSPITAL</h1>
            <h2>Laboratory Department</h2>
            <hr style='border: 2px solid #333; margin: 20px 0;'>
        </div>
        
        <div class='patient-info'>
            <table style='width: 100%; margin-bottom: 20px; border-collapse: collapse;'>
                <tr>
                    <td style='width: 50%; padding: 5px;'><strong>Patient Name:</strong> {$order->patient_name}</td>
                    <td style='width: 50%; padding: 5px;'><strong>Patient No:</strong> {$order->patient_no}</td>
                </tr>
                <tr>
                    <td style='padding: 5px;'><strong>Test Requested:</strong> {$order->test_requested}</td>
                    <td style='padding: 5px;'><strong>Date:</strong> " . now()->format('F d, Y') . "</td>
                </tr>
                <tr>
                    <td style='padding: 5px;'><strong>Requested By:</strong> " . ($order->requestedBy->name ?? 'N/A') . "</td>
                    <td style='padding: 5px;'><strong>Lab Technician:</strong> {$resultsData['completed_by']}</td>
                </tr>
            </table>
        </div>";

        if ($testType === 'cbc') {
            $html .= $this->generateCBCPDFContent($resultsData);
        } elseif ($testType === 'chemistry') {
            $html .= $this->generateChemistryPDFContent($resultsData);
        } elseif ($testType === 'urinalysis') {
            $html .= $this->generateUrinalysisPDFContent($resultsData);
        } else {
            $html .= "
            <div class='results-section'>
                <h3>TEST RESULTS</h3>
                <div style='border: 1px solid #333; padding: 15px; min-height: 200px;'>
                    " . nl2br(htmlspecialchars($resultsData['general_results'] ?? '')) . "
                </div>
            </div>";
        }

        if (!empty($resultsData['interpretation'])) {
            $html .= "
            <div class='interpretation-section'>
                <h3>CLINICAL INTERPRETATION</h3>
                <div style='border: 1px solid #333; padding: 15px; min-height: 100px;'>
                    " . nl2br(htmlspecialchars($resultsData['interpretation'])) . "
                </div>
            </div>";
        }

        if (!empty($resultsData['notes'])) {
            $html .= "
            <div class='notes-section'>
                <h3>ADDITIONAL NOTES</h3>
                <div style='border: 1px solid #333; padding: 15px; min-height: 80px;'>
                    " . nl2br(htmlspecialchars($resultsData['notes'])) . "
                </div>
            </div>";
        }

        $html .= "
        <div class='footer' style='margin-top: 30px; text-align: center; font-size: 0.9em; color: #666;'>
            <p>This is an electronically generated laboratory result.</p>
            <p>Date Generated: " . now()->format('F d, Y H:i A') . "</p>
        </div>";

        return $html;
    }

    /**
     * Generate CBC-specific PDF content
     */
    private function generateCBCPDFContent($resultsData)
    {
        return "
        <div class='results-section'>
            <h3>COMPLETE BLOOD COUNT (CBC)</h3>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                <thead>
                    <tr style='background: #333; color: white;'>
                        <th style='border: 1px solid #333; padding: 10px; text-align: left;'>TEST</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>RESULT</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>UNIT</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>REFERENCE RANGE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>HEMATOLOGY</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Red Blood Cell Count</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['rbc_count']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>x10⁶/μL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>4.5 - 5.5</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Hemoglobin</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['hemoglobin']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>g/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>12.0 - 16.0</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Hematocrit</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['hematocrit']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>%</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>36.0 - 46.0</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Platelet Count</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['platelet_count']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>x10³/μL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>150 - 400</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>White Blood Cell Count</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['wbc_count']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>x10³/μL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>4.5 - 11.0</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>RED CELL INDICES</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Mean Corpuscular Volume (MCV)</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['mcv']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>fL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>80.0 - 95.0</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Mean Corpuscular Hemoglobin (MCH)</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['mch']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>pg</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>27.0 - 33.0</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>MCHC</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['mchc']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>g/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>32.0 - 36.0</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>DIFFERENTIAL COUNT</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Neutrophils</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['neutrophils']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>%</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>50.0 - 70.0</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Lymphocytes</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['lymphocytes']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>%</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>20.0 - 40.0</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Monocytes</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['monocytes']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>%</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>2.0 - 8.0</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Eosinophils</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['eosinophils']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>%</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>1.0 - 4.0</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Basophils</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['basophils']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>%</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>0.0 - 2.0</td>
                    </tr>
                </tbody>
            </table>
        </div>";
    }

    /**
     * Generate Chemistry-specific PDF content
     */
    private function generateChemistryPDFContent($resultsData)
    {
        return "
        <div class='results-section'>
            <h3>CLINICAL CHEMISTRY</h3>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                <thead>
                    <tr style='background: #333; color: white;'>
                        <th style='border: 1px solid #333; padding: 10px; text-align: left;'>TEST</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>RESULT</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>UNIT</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>REFERENCE RANGE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>GLUCOSE</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Random Blood Sugar (RBS)</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['rbs']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>< 140</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Fasting Blood Sugar (FBS)</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['fbs']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>70 - 100</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>LIPID PROFILE</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Total Cholesterol</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['total_cholesterol']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>< 200</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>LDL Cholesterol</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['ldl_cholesterol']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>< 100</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>HDL Cholesterol</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['hdl_cholesterol']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>40 - 60</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Triglycerides</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['triglycerides']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>< 150</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>LIVER FUNCTION</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Total Bilirubin</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['total_bilirubin']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>0.2 - 1.2</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Direct Bilirubin</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['direct_bilirubin']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>0.0 - 0.3</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>SGPT/ALT</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['sgpt_alt']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>U/L</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>7 - 56</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>SGOT/AST</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['sgot_ast']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>U/L</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>8 - 40</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>KIDNEY FUNCTION</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Blood Urea Nitrogen (BUN)</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['bun']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>7 - 20</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Creatinine</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['creatinine']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>0.6 - 1.3</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Uric Acid</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['uric_acid']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>mg/dL</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>3.5 - 7.2</td>
                    </tr>
                </tbody>
            </table>
        </div>";
    }

    /**
     * Generate Urinalysis-specific PDF content
     */
    private function generateUrinalysisPDFContent($resultsData)
    {
        return "
        <div class='results-section'>
            <h3>URINALYSIS</h3>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                <thead>
                    <tr style='background: #333; color: white;'>
                        <th style='border: 1px solid #333; padding: 10px; text-align: left;'>TEST</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>RESULT</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>UNIT</th>
                        <th style='border: 1px solid #333; padding: 10px; text-align: center;'>REFERENCE RANGE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>PHYSICAL EXAMINATION</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Color</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['urine_color']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Pale Yellow to Yellow</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Clarity</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['urine_clarity']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Clear</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Specific Gravity</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['specific_gravity']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>1.003 - 1.030</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>CHEMICAL EXAMINATION</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>pH</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['urine_ph']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>5.0 - 8.0</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Protein</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['protein']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Negative</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Glucose</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['glucose']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Negative</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Ketones</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['ketones']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Negative</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Blood</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['blood']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Negative</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Bilirubin</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['bilirubin']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Negative</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Urobilinogen</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['urobilinogen']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Normal</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Nitrite</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['nitrite']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Negative</td>
                    </tr>
                    <tr style='background: #f0f0f0; font-weight: bold;'>
                        <td colspan='4' style='border: 1px solid #333; padding: 8px;'>MICROSCOPIC EXAMINATION</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Red Blood Cells</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['urine_rbc']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>/hpf</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>0 - 2</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>White Blood Cells</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['urine_wbc']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>/hpf</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>0 - 5</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Epithelial Cells</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['epithelial_cells']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>/hpf</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Few</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Casts</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['casts']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>/lpf</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>None</td>
                    </tr>
                    <tr style='background: #f8f9fa;'>
                        <td style='border: 1px solid #333; padding: 8px;'>Crystals</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['crystals']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>/hpf</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Few to None</td>
                    </tr>
                    <tr>
                        <td style='border: 1px solid #333; padding: 8px;'>Bacteria</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>{$resultsData['bacteria']}</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>-</td>
                        <td style='border: 1px solid #333; padding: 8px; text-align: center;'>Few</td>
                    </tr>
                </tbody>
            </table>
        </div>";
    }
}
