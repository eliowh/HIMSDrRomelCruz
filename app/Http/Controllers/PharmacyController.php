<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\StockOrder;
use App\Models\StockPrice;
use App\Models\PharmacyStock;
use App\Models\StocksReference;
use App\Models\PharmacyRequest;
use App\Models\Patient;
use App\Models\PatientMedicine;

class PharmacyController extends Controller
{
    public function orders(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = StockOrder::with('user')
            ->forUser(Auth::id())
            ->orderBy('requested_at', 'desc');
        
        // Filter by status if specified
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $orders = $query->paginate(15)->appends($request->query());
        
        // Get counts for each status
        $statusCounts = [
            'all' => StockOrder::forUser(Auth::id())->count(),
            'pending' => StockOrder::forUser(Auth::id())->pending()->count(),
            'approved' => StockOrder::forUser(Auth::id())->approved()->count(),
            'completed' => StockOrder::forUser(Auth::id())->completed()->count(),
            'cancelled' => StockOrder::forUser(Auth::id())->cancelled()->count(),
        ];
        
        return view('pharmacy.pharmacy_orders', compact('orders', 'statusCounts', 'status'));
    }
    
    public function storeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string',
            'generic_name' => 'nullable|string',
            'brand_name' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Custom validation: at least one of generic_name or brand_name must be provided
        $validator->after(function ($validator) use ($request) {
            if (empty(trim($request->generic_name)) && empty(trim($request->brand_name))) {
                $validator->errors()->add('medicine_name', 'Either generic name or brand name must be provided.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stockOrder = new StockOrder([
                'user_id' => Auth::id(),
                'item_code' => $request->item_code,
                'generic_name' => trim($request->generic_name) ?: null,
                'brand_name' => trim($request->brand_name) ?: null,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'notes' => $request->notes,
                'requested_at' => now(),
            ]);
            
            $stockOrder->calculateTotalPrice();
            $stockOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Order request submitted successfully',
                'order' => $stockOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit order request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateOrder(Request $request, $id)
    {
        // Add debugging
        \Log::info('Update Order Request:', [
            'id' => $id,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'all_data' => $request->all(),
            'json_data' => $request->json()->all(),
            'has_quantity' => $request->has('quantity'),
            'quantity_value' => $request->get('quantity'),
            'user_id' => Auth::id()
        ]);

        // Handle both form data and JSON data
        $data = $request->all();
        if ($request->isJson()) {
            $data = $request->json()->all();
        }

        $validator = Validator::make($data, [
            'quantity' => 'sometimes|integer|min:1',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            \Log::error('Update Order Validation Failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stockOrder = StockOrder::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', StockOrder::STATUS_PENDING)
                ->first();

            if (!$stockOrder) {
                \Log::error('Order not found for update:', ['id' => $id, 'user_id' => Auth::id()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or cannot be updated'
                ], 404);
            }

            \Log::info('Order found, updating:', [
                'order_id' => $stockOrder->id,
                'old_quantity' => $stockOrder->quantity,
                'new_quantity' => $data['quantity'] ?? null
            ]);

            if (isset($data['quantity'])) {
                $stockOrder->quantity = $data['quantity'];
                $stockOrder->calculateTotalPrice();
            }

            if (isset($data['notes'])) {
                $stockOrder->notes = $data['notes'];
            }

            $stockOrder->save();

            \Log::info('Order updated successfully:', [
                'order_id' => $stockOrder->id,
                'final_quantity' => $stockOrder->quantity,
                'final_total_price' => $stockOrder->total_price
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => $stockOrder
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Order Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteOrder($id)
    {
        try {
            $stockOrder = StockOrder::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', StockOrder::STATUS_PENDING)
                ->first();

            if (!$stockOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or cannot be deleted'
                ], 404);
            }

            $stockOrder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function cancelOrder(Request $request, $id)
    {
        try {
            $stockOrder = StockOrder::where('id', $id)
                ->where('user_id', Auth::id())
                ->whereIn('status', [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED])
                ->first();

            if (!$stockOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or cannot be cancelled'
                ], 404);
            }

            $stockOrder->status = StockOrder::STATUS_CANCELLED;
            $stockOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    // API endpoints for dropdown population
    public function getStocksReference(Request $request)
    {
        $search = $request->get('search', '');
        $type = $request->get('type', 'all'); // 'item_code', 'generic_name', 'brand_name', or 'all'
        
        $query = StocksReference::excludeHeader(); // Exclude header row and show all items
        
        if ($search) {
            switch ($type) {
                case 'item_code':
                    $query->where('COL 1', 'like', '%' . $search . '%');
                    break;
                case 'generic_name':
                    $query->whereNotNull('COL 2')
                          ->where('COL 2', '!=', '')
                          ->where('COL 2', 'like', '%' . $search . '%');
                    break;
                case 'brand_name':
                    $query->whereNotNull('COL 3')
                          ->where('COL 3', '!=', '')
                          ->where('COL 3', 'like', '%' . $search . '%');
                    break;
                default:
                    $query->where(function($q) use ($search) {
                        $q->where('COL 1', 'like', '%' . $search . '%')
                          ->orWhere(function($subQ) use ($search) {
                              $subQ->whereNotNull('COL 2')
                                   ->where('COL 2', '!=', '')
                                   ->where('COL 2', 'like', '%' . $search . '%');
                          })
                          ->orWhere(function($subQ) use ($search) {
                              $subQ->whereNotNull('COL 3')
                                   ->where('COL 3', '!=', '')
                                   ->where('COL 3', 'like', '%' . $search . '%');
                          });
                    });
            }
        }
        
        $stocks = $query->limit(50)->get();
        
        return response()->json([
            'success' => true,
            'data' => $stocks->map(function($stock) {
                return [
                    'id' => $stock->id,
                    'item_code' => $stock->item_code, // Uses accessor method
                    'generic_name' => $stock->generic_name ?? '', // Uses accessor method
                    'brand_name' => $stock->brand_name ?? '', // Uses accessor method
                    'price' => $stock->price, // Uses accessor method
                    'quantity_available' => 999, // Since stocksreference doesn't track quantity, use default
                ];
            })
        ]);
    }

    public function getStockByItemCode($itemCode)
    {
        $stock = StockPrice::where('item_code', $itemCode)
                          ->where('quantity', '>', 0)
                          ->first();
        
        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found or out of stock'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $stock->id,
                'item_code' => $stock->item_code,
                'generic_name' => $stock->generic_name ?? '',
                'brand_name' => $stock->brand_name ?? '',
                'price' => $stock->price,
                'quantity_available' => $stock->quantity,
            ]
        ]);
    }

    public function home()
    {
        $userId = Auth::id();
        
        // Get pharmacy order statistics
        $pendingOrders = StockOrder::forUser($userId)->pending()->count();
        $approvedOrders = StockOrder::forUser($userId)->approved()->count();
        $completedOrders = StockOrder::forUser($userId)->completed()->count();
        $cancelledOrders = StockOrder::forUser($userId)->cancelled()->count();
        $totalOrders = StockOrder::forUser($userId)->count();
        
        // Get recent pharmacy orders (last 5)
        $recentOrders = StockOrder::with('user')
            ->forUser($userId)
            ->orderBy('requested_at', 'desc')
            ->limit(5)
            ->get();
        
        // Calculate total value of pending orders
        $pendingOrdersValue = StockOrder::forUser($userId)
            ->pending()
            ->sum('total_price');
        
        // Calculate total value of completed orders this month
        $completedOrdersValue = StockOrder::forUser($userId)
            ->completed()
            ->whereMonth('requested_at', now()->month)
            ->whereYear('requested_at', now()->year)
            ->sum('total_price');
        
        // Get low stock items (you can adjust this logic based on your needs)
        $lowStockCount = 0; // Placeholder - you can implement this based on your stock management
        
        return view('pharmacy.pharmacy_home', compact(
            'pendingOrders',
            'approvedOrders', 
            'completedOrders',
            'cancelledOrders',
            'totalOrders',
            'recentOrders',
            'pendingOrdersValue',
            'completedOrdersValue',
            'lowStockCount'
        ));
    }

    /**
     * Show nurse-submitted medicine requests for pharmacy staff
     */
    public function nurseRequests(Request $request)
    {
        $q = $request->get('q', '');
        $status = $request->get('status', 'all');
        $sort = $request->get('sort', 'requested_at');
        $direction = $request->get('direction', 'desc');
        $perPage = $request->get('per_page', 10);

        try {
            // Main (active) requests: exclude dispensed & completed when viewing 'all'
            $query = PharmacyRequest::with(['requestedBy', 'patient']);

            $includeCompletedSection = ($status === 'all');
            if ($status === 'all') {
                $query->whereNotIn('status', [
                    PharmacyRequest::STATUS_DISPENSED,
                    PharmacyRequest::STATUS_COMPLETED,
                ]);
            } else {
                // If specific status requested, filter directly (may include dispensed/completed)
                $query->where('status', $status);
                $includeCompletedSection = false; // don't show history section when explicitly filtered
            }

            // Add search functionality like stocks
            if ($q) {
                $query->where(function($builder) use ($q) {
                    $builder->where('item_code', 'like', "%{$q}%")
                            ->orWhere('generic_name', 'like', "%{$q}%")
                            ->orWhere('brand_name', 'like', "%{$q}%")
                            ->orWhere('patient_name', 'like', "%{$q}%")
                            ->orWhere('patient_no', 'like', "%{$q}%");
                });
            }

            // (status filtering already applied above)

            // Add sorting functionality
            $allowedSorts = ['requested_at', 'patient_name', 'generic_name', 'brand_name', 'status', 'quantity', 'priority'];
            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('requested_at', 'desc');
            }

            $requests = $query->paginate($perPage)->appends($request->query());

            // History (completed/dispensed) requests list
            $completedRequests = collect();
            if ($includeCompletedSection) {
                $completedRequests = PharmacyRequest::with(['requestedBy', 'patient', 'dispensedBy'])
                    ->whereIn('status', [
                        PharmacyRequest::STATUS_DISPENSED,
                        PharmacyRequest::STATUS_COMPLETED,
                    ])
                    ->when($q, function($builder) use ($q) {
                        $builder->where(function($b) use ($q) {
                            $b->where('item_code', 'like', "%{$q}%")
                              ->orWhere('generic_name', 'like', "%{$q}%")
                              ->orWhere('brand_name', 'like', "%{$q}%")
                              ->orWhere('patient_name', 'like', "%{$q}%")
                              ->orWhere('patient_no', 'like', "%{$q}%");
                        });
                    })
                    ->orderByRaw('COALESCE(dispensed_at, completed_at, updated_at) DESC')
                    ->limit(50)
                    ->get();
            }

            $statusCounts = [
                'all' => PharmacyRequest::count(),
                'pending' => PharmacyRequest::where('status', PharmacyRequest::STATUS_PENDING)->count(),
                'in_progress' => PharmacyRequest::where('status', PharmacyRequest::STATUS_IN_PROGRESS)->count(),
                'completed' => PharmacyRequest::where('status', PharmacyRequest::STATUS_COMPLETED)->count(),
                'cancelled' => PharmacyRequest::where('status', PharmacyRequest::STATUS_CANCELLED)->count(),
                'dispensed' => PharmacyRequest::where('status', PharmacyRequest::STATUS_DISPENSED)->count(),
            ];

            return view('pharmacy.pharmacy_requests', compact('requests', 'completedRequests', 'statusCounts', 'q', 'status', 'sort', 'direction', 'perPage'));
        } catch (\Throwable $e) {
            // Log the error and return an empty collection so the page doesn't crash.
            \Log::error('Pharmacy requests load failed: ' . $e->getMessage());

            // Create a paginator from an empty array
            $requests = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => $request->url()]
            );
            $dbError = $e->getMessage();
            $statusCounts = [
                'all' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'dispensed' => 0,
            ];
            $completedRequests = collect();
            return view('pharmacy.pharmacy_requests', compact('requests', 'completedRequests', 'statusCounts', 'q', 'status', 'sort', 'direction', 'perPage', 'dbError'));
        }
    }

    /**
     * Show medicine request history for nurses (dispensed and cancelled requests)
     */
    public function nurseRequestHistory(Request $request)
    {
        $q = $request->get('q', '');
        $status = $request->get('status', 'all');
        $sort = $request->get('sort', 'dispensed_at');
        $direction = $request->get('direction', 'desc');
        $perPage = $request->get('per_page', 15);

        try {
            // Show only dispensed and cancelled requests for nurse history
            $query = PharmacyRequest::with(['requestedBy', 'patient', 'dispensedBy', 'cancelledBy'])
                ->whereIn('status', [PharmacyRequest::STATUS_DISPENSED, PharmacyRequest::STATUS_CANCELLED]);

            // Add search functionality
            if ($q) {
                $query->where(function($builder) use ($q) {
                    $builder->where('item_code', 'like', "%{$q}%")
                            ->orWhere('generic_name', 'like', "%{$q}%")
                            ->orWhere('brand_name', 'like', "%{$q}%")
                            ->orWhere('patient_name', 'like', "%{$q}%")
                            ->orWhere('patient_no', 'like', "%{$q}%");
                });
            }

            // Add status filtering
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Add sorting functionality
            $allowedSorts = ['requested_at', 'dispensed_at', 'cancelled_at', 'patient_name', 'generic_name', 'brand_name', 'status', 'quantity'];
            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
            } else {
                // Default sort by most recent processed date
                $query->orderByRaw('COALESCE(dispensed_at, cancelled_at) DESC');
            }

            $requests = $query->paginate($perPage)->appends($request->query());

            $statusCounts = [
                'all' => PharmacyRequest::whereIn('status', [PharmacyRequest::STATUS_DISPENSED, PharmacyRequest::STATUS_CANCELLED])->count(),
                'dispensed' => PharmacyRequest::where('status', PharmacyRequest::STATUS_DISPENSED)->count(),
                'cancelled' => PharmacyRequest::where('status', PharmacyRequest::STATUS_CANCELLED)->count(),
            ];

            return view('nurse.medicine_request_history', compact('requests', 'statusCounts', 'q', 'status', 'sort', 'direction', 'perPage'));
        } catch (\Throwable $e) {
            // Log the error and return an empty collection so the page doesn't crash.
            \Log::error('Nurse request history load failed: ' . $e->getMessage());

            // Create a paginator from an empty array
            $requests = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => $request->url()]
            );
            $dbError = $e->getMessage();
            $statusCounts = [
                'all' => 0,
                'dispensed' => 0,
                'cancelled' => 0,
            ];
            return view('nurse.medicine_request_history', compact('requests', 'statusCounts', 'q', 'status', 'sort', 'direction', 'perPage', 'dbError'));
        }
    }

    /**
     * Show pharmacy stocks list from pharmacystocks table
     */
    public function stocksPharmacy(Request $request)
    {
        $q = $request->get('q', '');

        try {
            $stocksQuery = PharmacyStock::query();
            if ($q) {
                $stocksQuery->where(function($builder) use ($q) {
                    $builder->where('item_code', 'like', "%{$q}%")
                            ->orWhere('generic_name', 'like', "%{$q}%")
                            ->orWhere('brand_name', 'like', "%{$q}%");
                });
            }

            // Return paginated records and normalize null quantities to 0
            $stockspharmacy = $stocksQuery->orderBy('generic_name')->paginate(15);
            
            // Transform the paginated data to normalize quantities
            $stockspharmacy->getCollection()->transform(function($s) {
                $s->quantity = $s->quantity ?? 0;
                return $s;
            });

            return view('pharmacy.pharmacy_stocks', compact('stockspharmacy', 'q'));
        } catch (\Throwable $e) {
            // Log the error and return an empty collection so the page doesn't crash.
            \Log::error('Pharmacy stocks load failed: ' . $e->getMessage());

            // Create a paginator from an empty array
            $stockspharmacy = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => $request->url()]
            );
            $dbError = $e->getMessage();
            return view('pharmacy.pharmacy_stocks', compact('stockspharmacy', 'q', 'dbError'));
        }
    }

    /**
     * Alias for stocksPharmacy method - for route /pharmacy/stocks
     */
    public function stocks(Request $request)
    {
        return $this->stocksPharmacy($request);
    }

    /**
     * Store a nurse-submitted pharmacy order
     */
    public function storeNurseRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'item_code' => 'nullable|string',
            'generic_name' => 'nullable|string',
            'brand_name' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            // Get patient information
            $patient = Patient::findOrFail($request->patient_id);

            $pharmacyRequest = new PharmacyRequest([
                'patient_id' => $request->patient_id,
                'requested_by' => auth()->id(),
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'patient_no' => $patient->patient_no,
                'item_code' => $request->item_code ?: null,
                'generic_name' => $request->generic_name ?: null,
                'brand_name' => $request->brand_name ?: null,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price ?: 0,
                'notes' => $request->notes ?: null,
                'requested_at' => now(),
                'status' => PharmacyRequest::STATUS_PENDING,
                'priority' => PharmacyRequest::PRIORITY_NORMAL,
            ]);

            $pharmacyRequest->calculateTotalPrice();
            $pharmacyRequest->save();

            return response()->json(['success' => true, 'message' => 'Medicine request submitted to pharmacy', 'request' => $pharmacyRequest]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to submit request: ' . $e->getMessage()], 500);
        }
    }

    public function dispenseRequest($id)
    {
        \Log::info("Dispense request started for ID: {$id}");
        
        try {
            DB::beginTransaction();

            $request = PharmacyRequest::findOrFail($id);
            \Log::info("Found request: {$request->id}, Status: {$request->status}");
            
            if ($request->status !== PharmacyRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be dispensed'
                ], 400);
            }

            // Check if there's enough stock
            $pharmacyStock = PharmacyStock::where('item_code', $request->item_code)->first();
            
            // If not found by item_code, try to find by generic_name or brand_name
            if (!$pharmacyStock && $request->generic_name) {
                $pharmacyStock = PharmacyStock::where('generic_name', $request->generic_name)->first();
            }
            
            if (!$pharmacyStock && $request->brand_name) {
                $pharmacyStock = PharmacyStock::where('brand_name', $request->brand_name)->first();
            }
            
            if (!$pharmacyStock || $pharmacyStock->quantity < $request->quantity) {
                $availableQty = $pharmacyStock ? $pharmacyStock->quantity : 0;
                $medicineName = $request->generic_name ?: ($request->brand_name ?: $request->item_code);
                
                \Log::info("Insufficient stock - Medicine: {$medicineName}, Available: {$availableQty}, Required: {$request->quantity}");
                
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$medicineName}. Available: {$availableQty} units, Required: {$request->quantity} units. Please check inventory or reduce quantity."
                ], 400);
            }

            // Get patient information
            $patient = Patient::find($request->patient_id);
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found'
                ], 400);
            }

            // Update pharmacy stock
            $pharmacyStock->quantity -= $request->quantity;
            $pharmacyStock->save();

            // Create patient medicine record
            $patientMedicineData = [
                'patient_id' => $request->patient_id,
                'pharmacy_request_id' => $request->id,
                'patient_no' => $patient->patient_no,
                'patient_name' => $request->patient_name,
                'item_code' => $request->item_code,
                'generic_name' => $request->generic_name,
                'brand_name' => $request->brand_name,
                'quantity' => $request->quantity,
                'unit_price' => floatval($request->unit_price ?? 0),
                'total_price' => floatval($request->total_price ?? 0),
                'notes' => $request->notes,
                'dispensed_by' => auth()->id(),
                'dispensed_at' => now(),
            ];
            
            $patientMedicine = PatientMedicine::create($patientMedicineData);

            // Update request status
            $updateData = [
                'status' => PharmacyRequest::STATUS_DISPENSED,
                'dispensed_at' => now()->format('Y-m-d H:i:s'),
                'dispensed_by' => auth()->id(),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ];
            
            PharmacyRequest::where('id', $request->id)->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medicine dispensed successfully. Stock updated and medicine attached to patient record.',
                'patient_medicine_id' => $patientMedicine->id,
                'remaining_stock' => $pharmacyStock->quantity
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispense medicine: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelRequest($id)
    {
        try {
            DB::beginTransaction();

            $request = PharmacyRequest::findOrFail($id);
            
            if ($request->status !== PharmacyRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be cancelled. Current status: ' . ucfirst(str_replace('_', ' ', $request->status))
                ], 400);
            }

            // Update request status
            $request->status = PharmacyRequest::STATUS_CANCELLED;
            $request->cancelled_at = now();
            $request->cancelled_by = auth()->id();
            $request->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medicine request cancelled successfully. No stock changes were made.',
                'request_id' => $request->id,
                'cancelled_by' => auth()->user()->name,
                'cancelled_at' => $request->cancelled_at->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all patient medicines (dispensed medicines)
     */
    public function patientMedicines(Request $request)
    {
        try {
            $query = PatientMedicine::with(['patient', 'dispensedBy', 'pharmacyRequest'])
                ->orderBy('dispensed_at', 'desc');

            // Add search functionality
            if ($search = $request->get('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('patient_name', 'like', "%{$search}%")
                      ->orWhere('patient_no', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%")
                      ->orWhere('brand_name', 'like', "%{$search}%")
                      ->orWhere('item_code', 'like', "%{$search}%");
                });
            }

            // Add date filter
            if ($date = $request->get('date')) {
                $query->whereDate('dispensed_at', $date);
            }

            $patientMedicines = $query->paginate(15);

            return view('pharmacy.patient_medicines', compact('patientMedicines'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load patient medicines: ' . $e->getMessage());
        }
    }

    /**
     * Display medicines for a specific patient
     */
    public function patientMedicinesByPatient($patientId)
    {
        try {
            $patient = Patient::findOrFail($patientId);
            $medicines = PatientMedicine::with(['dispensedBy', 'pharmacyRequest'])
                ->where('patient_id', $patientId)
                ->orderBy('dispensed_at', 'desc')
                ->paginate(15);

            return view('pharmacy.patient_medicines_by_patient', compact('patient', 'medicines'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load patient medicines: ' . $e->getMessage());
        }
    }

    public function showRequest($id)
    {
        try {
            $request = PharmacyRequest::with(['patient', 'requestedBy', 'pharmacist', 'dispensedBy'])->findOrFail($id);
            
            // Get patient name - try relationship first, then patient_name field
            $patientName = 'Unknown Patient';
            if ($request->patient) {
                $patientName = $request->patient->full_name ?? ($request->patient->first_name . ' ' . $request->patient->last_name);
            } elseif ($request->patient_name) {
                $patientName = $request->patient_name;
            }
            
            // Format medicine name
            $medicineName = $request->generic_name ?: $request->brand_name ?: 'Unknown Medicine';
            if ($request->generic_name && $request->brand_name) {
                $medicineName = $request->generic_name . ' (' . $request->brand_name . ')';
            }
            
            return response()->json([
                'success' => true,
                'request' => [
                    'id' => $request->id,
                    'patient_id' => $request->patient_id,
                    'patient_name' => $patientName,
                    'patient_no' => $request->patient_no,
                    'medicine_name' => $medicineName,
                    'generic_name' => $request->generic_name,
                    'brand_name' => $request->brand_name,
                    'item_code' => $request->item_code,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'total_price' => $request->total_price,
                    'notes' => $request->notes ?: 'No additional notes',
                    'status' => ucfirst($request->status),
                    'priority' => ucfirst($request->priority ?: 'normal'),
                    'requested_at' => $request->requested_at ? $request->requested_at->format('M d, Y h:i A') : null,
                    'requested_by' => $request->requestedBy ? $request->requestedBy->name : 'Unknown',
                    'pharmacist' => $request->pharmacist ? $request->pharmacist->name : null,
                    'dispensed_by' => $request->dispensedBy ? $request->dispensedBy->name : null,
                    'dispensed_at' => $request->dispensed_at ? $request->dispensed_at->format('M d, Y h:i A') : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load request details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get patient medicines for AJAX calls
     */
    public function getPatientMedicinesApi($patientId)
    {
        try {
            $medicines = PatientMedicine::with(['dispensedBy'])
                ->where('patient_id', $patientId)
                ->orderBy('dispensed_at', 'desc')
                ->get();

            $formattedMedicines = $medicines->map(function($medicine) {
                return [
                    'id' => $medicine->id,
                    'generic_name' => $medicine->generic_name,
                    'brand_name' => $medicine->brand_name,
                    'item_code' => $medicine->item_code,
                    'quantity' => $medicine->quantity,
                    'unit_price' => $medicine->unit_price,
                    'total_price' => $medicine->total_price,
                    'notes' => $medicine->notes,
                    'dispensed_at' => $medicine->dispensed_at ? $medicine->dispensed_at->format('M d, Y h:i A') : null,
                    'dispensed_by' => $medicine->dispensedBy ? $medicine->dispensedBy->name : 'Unknown',
                    'medicine_name' => $medicine->generic_name ?: $medicine->brand_name ?: 'Unknown Medicine'
                ];
            });

            return response()->json([
                'success' => true,
                'medicines' => $formattedMedicines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load patient medicines: ' . $e->getMessage()
            ], 500);
        }
    }
}