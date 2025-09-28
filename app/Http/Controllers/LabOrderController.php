<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
            'priority' => 'required|in:normal,urgent,stat'
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
                $path = $file->storeAs('lab-results', $filename, 'public');
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
            'pdf_url' => $order->results_pdf_path ? Storage::url($order->results_pdf_path) : null
        ]);
    }

    public function downloadPdf($id)
    {
        $order = LabOrder::findOrFail($id);
        
        if (!$order->results_pdf_path || !Storage::disk('public')->exists($order->results_pdf_path)) {
            return response()->json(['error' => 'PDF file not found'], 404);
        }
        
        return Storage::disk('public')->download($order->results_pdf_path, 
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
            
            return response()->json([
                'success' => true,
                'tests' => $testHistory
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching patient test history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving test history: ' . $e->getMessage()
            ], 500);
        }
    }
}
