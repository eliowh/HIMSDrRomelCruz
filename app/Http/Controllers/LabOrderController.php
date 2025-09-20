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
    public function index()
    {
        $orders = LabOrder::with(['patient', 'requestedBy', 'labTech'])
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        return view('labtech.labtech_orders', compact('orders'));
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
                'send_to_doctor' => 'nullable|in:on,1,true,0,false'
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
            
            // Handle PDF upload
            if ($request->hasFile('results_pdf')) {
                $file = $request->file('results_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('lab-results', $filename, 'public');
                $updateData['results_pdf_path'] = $path;
            }
            
            // Set lab tech who completed the order
            $updateData['lab_tech_id'] = auth()->id();
        }

        $order->update($updateData);
        
        // If send_to_doctor is checked and order is completed, you could add notification logic here
        $sendToDoctor = in_array($request->send_to_doctor, ['on', '1', 'true', true], true);
        if ($sendToDoctor && $request->status === 'completed') {
            // TODO: Add notification to requesting doctor
            // This could send an email or create a notification record
        }

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
}
