<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\StockOrder;
use App\Models\StockPrice;
use App\Models\PharmacyStock;
use App\Models\StocksReference;
use App\Models\PharmacyRequest;
use App\Models\Patient;
use App\Models\PatientMedicine;
use App\Models\Report;

class PharmacyController extends Controller
{
    /**
     * Parse price values that may contain commas, currency symbols
     */
    private function parsePrice($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        if (is_string($value)) {
            // Remove commas, currency symbols, and spaces, then parse
            $cleaned = preg_replace('/[,₱$\s]/', '', $value);
            $parsed = floatval($cleaned);
            return $parsed;
        }
        
        return 0;
    }

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
            'unit_price' => 'required', // Allow string or numeric to handle comma-separated values
            'notes' => 'nullable|string|max:1000',
        ]);

        // Custom validation: at least one of generic_name or brand_name must be provided
        $validator->after(function ($validator) use ($request) {
            if (empty(trim($request->generic_name)) && empty(trim($request->brand_name))) {
                $validator->errors()->add('medicine_name', 'Either generic name or brand name must be provided.');
            }

            // Validate that unit_price can be parsed as a positive number
            $parsedPrice = $this->parsePrice($request->unit_price);
            if ($parsedPrice < 0) {
                $validator->errors()->add('unit_price', 'Unit price must be a positive number.');
            }

            // Validate against stocks reference masterlist
            $this->validateStockReference($validator, $request);
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Parse unit price to handle commas and currency symbols
            $unitPrice = $this->parsePrice($request->unit_price);

            $stockOrder = new StockOrder([
                'user_id' => Auth::id(),
                'item_code' => $request->item_code,
                'generic_name' => trim($request->generic_name) ?: null,
                'brand_name' => trim($request->brand_name) ?: null,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
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
            // Support an exact item_code match when requested (client uses this for Enter/blur lookups)
            if ($type === 'item_code_exact') {
                $query->where('COL 1', $search);
            } else {
                switch ($type) {
                    case 'item_code':
                        // For item_code queries, allow partial matches so autocomplete works when typing prefixes
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
        }

        // Ensure returned reference items include at least a generic name or a brand name
        // This normalizes the masterlist so clients receive well-formed suggestion objects
        $query->where(function($q2){
            $q2->whereNotNull('COL 2')->where('COL 2','!=','')
               ->orWhereNotNull('COL 3')->where('COL 3','!=','');
        });

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
        
        // Stock metrics for dashboard (pharmacy-specific)
        try {
            $totalStocks = PharmacyStock::count();
            // Low stock when quantity is less than or equal to reorder_level
            $lowStockCount = PharmacyStock::whereColumn('quantity', '<=', 'reorder_level')->count();
            $outOfStockCount = PharmacyStock::where('quantity', '<=', 0)->count();
            $recentStocks = PharmacyStock::orderBy('id', 'desc')->limit(5)->get();
            $totalStockValue = PharmacyStock::selectRaw('SUM(quantity * price) as total_value')->value('total_value') ?? 0;
            // Provide actual low stock items for potential auto-reorder (limit to 100)
            $lowStockItems = PharmacyStock::whereColumn('quantity', '<=', 'reorder_level')
                ->orderBy('quantity', 'asc')
                ->limit(100)
                ->get();

            // Expiring soon items (within 30 days)
            $expiringSoonItems = PharmacyStock::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [Carbon::now()->toDateString(), Carbon::now()->addDays(30)->toDateString()])
                ->orderBy('expiry_date', 'asc')
                ->limit(50)
                ->get();

            // Auto-reorder has been moved to a scheduled Artisan command: `php artisan pharmacy:auto-reorder`.
            // Running the auto-reorder from a command keeps the controller idempotent and avoids side-effects on page loads.
        } catch (\Throwable $e) {
            \Log::error('Pharmacy dashboard stock metrics failed: '.$e->getMessage());
            $totalStocks = 0;
            $lowStockCount = 0;
            $outOfStockCount = 0;
            $recentStocks = collect();
            $totalStockValue = 0;
            $lowStockItems = collect();
        }

        return view('pharmacy.pharmacy_home', compact(
            'pendingOrders',
            'approvedOrders', 
            'completedOrders',
            'cancelledOrders',
            'totalOrders',
            'recentOrders',
            'pendingOrdersValue',
            'completedOrdersValue',
            'totalStocks',
            'lowStockCount',
            'outOfStockCount',
            'recentStocks',
            'lowStockItems',
            'expiringSoonItems',
            'totalStockValue'
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
     * Add or increase a pharmacy stock item. Allows pharmacy staff to manage their own stocks.
     */
    public function addPharmacyStock(Request $request)
    {
        $data = $request->validate([
            'item_code' => ['required','string','max:100'],
            'generic_name' => ['nullable','string','max:255'],
            'brand_name' => ['nullable','string','max:255'],
            'quantity' => ['required','integer','min:1'],
            'price' => ['required','numeric','min:0'],
            'reorder_level' => ['nullable','integer','min:0'],
            'expiry_date' => ['nullable','date'],
            'supplier' => ['nullable','string','max:255'],
            'batch_number' => ['nullable','string','max:100'],
            'date_received' => ['nullable','date'],
        ]);

        try {
            // Validate against stocks reference if available, unless caller marked this as a custom medicine
            $isCustom = $request->boolean('custom_medicine');
            $referenceStock = null;
            if (!$isCustom) {
                $referenceStock = StocksReference::excludeHeader()
                    ->where('COL 1', $data['item_code'])
                    ->first();

                if (!$referenceStock) {
                    return response()->json(['ok' => false, 'message' => 'Item code not found in reference database.'], 422);
                }

                // Merge defaults from reference when needed
                $data['generic_name'] = $data['generic_name'] ?: ($referenceStock['COL 2'] ?? null);
                $data['brand_name'] = $data['brand_name'] ?: ($referenceStock['COL 3'] ?? null);
            }

            $stock = PharmacyStock::where('item_code', $data['item_code'])->first();

            if ($stock) {
                $stock->quantity = ($stock->quantity ?? 0) + intval($data['quantity']);
                $stock->price = $data['price'];
                $stock->reorder_level = $data['reorder_level'] ?? $stock->reorder_level;
                $stock->expiry_date = $data['expiry_date'] ?? $stock->expiry_date;
                $stock->supplier = $data['supplier'] ?? $stock->supplier;
                $stock->batch_number = $data['batch_number'] ?? $stock->batch_number;
                $stock->date_received = $data['date_received'] ?? $stock->date_received;
                if (!$stock->generic_name && $data['generic_name']) $stock->generic_name = $data['generic_name'];
                if (!$stock->brand_name && $data['brand_name']) $stock->brand_name = $data['brand_name'];
                $stock->save();

                try { Report::log('Pharmacy Stock Updated', Report::TYPE_USER_REPORT, 'Pharmacy stock increased', ['item_code'=>$stock->item_code,'added'=>$data['quantity'],'by'=>auth()->id()]); } catch (\Throwable $e) { \Log::error('Report log failed: '.$e->getMessage()); }

                return response()->json(['ok' => true, 'stock' => $stock, 'message' => 'Pharmacy stock updated successfully.']);
            }

            // If this was a custom medicine and it's not present in the master reference, add it there too
            if ($isCustom) {
                try {
                    $existsRef = StocksReference::excludeHeader()->where('COL 1', $data['item_code'])->first();
                    if (!$existsRef) {
                        // Use a direct table insert to avoid Eloquent timestamp/PK expectations on this legacy table
                        DB::table('stocksreference')->insert([
                            'COL 1' => $data['item_code'],
                            'COL 2' => $data['generic_name'] ?? '',
                            'COL 3' => $data['brand_name'] ?? '',
                            'COL 4' => is_numeric($data['price']) ? number_format((float)$data['price'], 2, '.', '') : ($data['price'] ?? 0),
                            'COL 5' => ''
                        ]);
                        try {
                            Report::log('Custom Masterlist Row Added', Report::TYPE_USER_REPORT, 'Custom medicine added to stocksreference', [
                                'item_code' => $data['item_code'],
                                'generic_name' => $data['generic_name'] ?? '',
                                'brand_name' => $data['brand_name'] ?? '',
                                'price' => is_numeric($data['price']) ? number_format((float)$data['price'], 2, '.', '') : ($data['price'] ?? 0),
                                'by' => auth()->id()
                            ]);
                        } catch (\Throwable $e) {
                            \Log::error('Report log failed: '.$e->getMessage());
                        }
                    }
                } catch (\Throwable $e) {
                    // Don't fail the add operation if reference insert fails; just log it
                    \Log::warning('Failed to add custom medicine to StocksReference: '.$e->getMessage());
                }
            }

            $new = PharmacyStock::create([
                'item_code' => $data['item_code'],
                'generic_name' => $data['generic_name'],
                'brand_name' => $data['brand_name'],
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'reorder_level' => $data['reorder_level'] ?? 10,
                'supplier' => $data['supplier'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'date_received' => $data['date_received'] ?? null,
            ]);

            try { Report::log('Pharmacy Stock Added', Report::TYPE_USER_REPORT, 'New pharmacy stock added', ['item_code'=>$new->item_code,'quantity'=>$new->quantity,'by'=>auth()->id()]); } catch (\Throwable $e) { \Log::error('Report log failed: '.$e->getMessage()); }

            return response()->json(['ok' => true, 'stock' => $new, 'message' => 'Pharmacy stock created successfully.']);

        } catch (\Exception $e) {
            \Log::error('addPharmacyStock error: '.$e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to add pharmacy stock: '.$e->getMessage()], 500);
        }
    }

    /**
     * Update a pharmacy stock item
     */
    public function updatePharmacyStock(Request $request, $id)
    {
        $data = $request->validate([
            'item_code' => ['nullable','string','max:100'],
            'generic_name' => ['nullable','string','max:255'],
            'brand_name' => ['nullable','string','max:255'],
            'price' => ['nullable','numeric','min:0'],
            'quantity' => ['nullable','integer','min:0'],
            'reorder_level' => ['nullable','integer','min:0'],
            'expiry_date' => ['nullable','date'],
            'supplier' => ['nullable','string','max:255'],
            'batch_number' => ['nullable','string','max:100'],
            'date_received' => ['nullable','date'],
        ]);

        try {
            $stock = PharmacyStock::find($id);
            if (!$stock) {
                $stock = PharmacyStock::where('item_code', $id)->first();
            }
            if (!$stock) return response()->json(['ok'=>false,'message'=>'Stock not found'],404);

            foreach ($data as $k=>$v) {
                if ($v !== null) $stock->$k = $v;
            }
            $stock->save();

            try { Report::log('Pharmacy Stock Edited', Report::TYPE_USER_REPORT, 'Pharmacy stock edited', ['item_code'=>$stock->item_code,'by'=>auth()->id()]); } catch (\Throwable $e) { \Log::error('Report log failed: '.$e->getMessage()); }

            return response()->json(['ok'=>true,'stock'=>$stock]);
        } catch (\Exception $e) {
            \Log::error('updatePharmacyStock error: '.$e->getMessage());
            return response()->json(['ok'=>false,'message'=>'Update failed: '.$e->getMessage()],500);
        }
    }

    /**
     * Delete a pharmacy stock item
     */
    public function deletePharmacyStock(Request $request, $id)
    {
        try {
            $stock = PharmacyStock::find($id);
            if (!$stock) $stock = PharmacyStock::where('item_code', $id)->first();
            if (!$stock) return response()->json(['ok'=>false,'message'=>'Stock not found'],404);

            $stock->delete();
            try { Report::log('Pharmacy Stock Deleted', Report::TYPE_USER_REPORT, 'Pharmacy stock deleted', ['item_code'=>$stock->item_code,'by'=>auth()->id()]); } catch (\Throwable $e) { \Log::error('Report log failed: '.$e->getMessage()); }

            return response()->json(['ok'=>true,'message'=>'Stock deleted']);
        } catch (\Exception $e) {
            \Log::error('deletePharmacyStock error: '.$e->getMessage());
            return response()->json(['ok'=>false,'message'=>'Delete failed: '.$e->getMessage()],500);
        }
    }

    /**
     * Store a nurse-submitted pharmacy order
     */
    public function storeNurseRequest(Request $request)
    {
        // Debug: Log the incoming request data
        \Log::info('Nurse pharmacy request received', [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'required|exists:admissions,id',
            // Require item_code to prevent empty medicine requests from nurses
            'item_code' => 'required|string',
            'generic_name' => 'nullable|string',
            'brand_name' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable', // Allow string or numeric to handle comma-separated values
            'notes' => 'nullable|string|max:1000',
        ]);

        // Custom validation for unit_price and stocks reference
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('unit_price')) {
                $parsedPrice = $this->parsePrice($request->unit_price);
                if ($parsedPrice < 0) {
                    $validator->errors()->add('unit_price', 'Unit price must be a positive number.');
                }
            }
            
            // Enforce stock reference validation for nurse-submitted requests so item_code must exist
            $this->validateStockReference($validator, $request);
        });

        if ($validator->fails()) {
            \Log::error('Nurse pharmacy request validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            // Get patient information
            $patient = Patient::findOrFail($request->patient_id);

            // Parse unit price to handle commas and currency symbols
            $unitPrice = $this->parsePrice($request->unit_price);

                // If item_code was provided, prefer authoritative values from stocks reference.
                // Important: we will NOT blindly copy the nurse-supplied 'generic_name' into the generic column
                // unless the masterlist indicates that the value is a generic. This avoids storing brand names
                // into the generic_name column when the masterlist has a brand-only row.
                $authoritativeGeneric = null;
                $authoritativeBrand = null;
                $authoritativePrice = null;
                $ref = null;
                if (!empty($request->item_code)) {
                    $ref = \App\Models\StocksReference::excludeHeader()->where('COL 1', $request->item_code)->first();
                    if ($ref) {
                        $g = trim((string)($ref->{'COL 2'} ?? ''));
                        $b = trim((string)($ref->{'COL 3'} ?? ''));
                        $p = trim((string)($ref->{'COL 4'} ?? ''));
                        // Keep generic NULL when masterlist has empty generic (brand-only rows)
                        $authoritativeGeneric = $g !== '' ? $g : null;
                        $authoritativeBrand = $b !== '' ? $b : null;
                        if ($p !== '') {
                            $authoritativePrice = $this->parsePrice($p);
                        }
                    }
                }

                // If authoritative price exists, use it as unit price (unless request provided explicit unit_price)
                if (!is_null($authoritativePrice) && (empty($request->unit_price) || $this->parsePrice($request->unit_price) <= 0)) {
                    $unitPrice = $authoritativePrice;
                }

                // If we found a masterlist row, use its generic/brand strictly (keep generic NULL when master has none).
                // If no master row was found (should be rare because item_code is required), try to infer whether the
                // supplied text corresponds to a generic or brand by searching exact matches in the masterlist. Only
                // set generic_name when the masterlist indicates it is a generic.
                $finalGeneric = null;
                $finalBrand = null;
                if ($ref) {
                    $finalGeneric = $authoritativeGeneric; // may be null intentionally
                    $finalBrand = $authoritativeBrand ?: (trim((string)($request->brand_name ?? '')) ?: null);
                } else {
                    // No authoritative row found by item_code. Try to infer from provided fields but prefer masterlist confirmation.
                    $providedGeneric = trim((string)($request->generic_name ?? '')) ?: null;
                    $providedBrand = trim((string)($request->brand_name ?? '')) ?: null;

                    // Check if providedGeneric exactly matches any master generic (case-insensitive)
                    if ($providedGeneric) {
                        $matchG = \App\Models\StocksReference::excludeHeader()
                            ->whereRaw('LOWER(`COL 2`) = ?', [mb_strtolower($providedGeneric)])
                            ->first();
                        if ($matchG) {
                            $finalGeneric = trim((string)($matchG->{'COL 2'} ?? '')) ?: null;
                        } else {
                            // See if the providedGeneric actually matches a brand in masterlist -> treat as brand
                            $matchAsBrand = \App\Models\StocksReference::excludeHeader()
                                ->whereRaw('LOWER(`COL 3`) = ?', [mb_strtolower($providedGeneric)])
                                ->first();
                            if ($matchAsBrand) {
                                $finalBrand = trim((string)($matchAsBrand->{'COL 3'} ?? '')) ?: null;
                            }
                        }
                    }

                    if ($providedBrand && !$finalBrand) {
                        $matchB = \App\Models\StocksReference::excludeHeader()
                            ->whereRaw('LOWER(`COL 3`) = ?', [mb_strtolower($providedBrand)])
                            ->first();
                        if ($matchB) {
                            $finalBrand = trim((string)($matchB->{'COL 3'} ?? '')) ?: null;
                        }
                    }

                    // As a last resort, if nothing matched, allow the provided brand text to be saved into brand_name
                    // so nurses can still record a recognizable value (but generic remains null unless confirmed).
                    if (!$finalBrand && $providedBrand) {
                        $finalBrand = $providedBrand;
                    }
                }

            $pharmacyRequest = new PharmacyRequest([
                'patient_id' => $request->patient_id,
                'admission_id' => $request->admission_id,
                'requested_by' => auth()->id(),
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'patient_no' => $patient->patient_no,
                'item_code' => $request->item_code ?: null,
                // Prefer authoritative masterlist values when available. finalGeneric/finalBrand are derived above.
                'generic_name' => $finalGeneric ?? null,
                'brand_name' => $finalBrand ?? null,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
                'notes' => $request->notes ?: null,
                'requested_at' => now(),
                'status' => PharmacyRequest::STATUS_PENDING,
                'priority' => PharmacyRequest::PRIORITY_NORMAL,
            ]);

            $pharmacyRequest->calculateTotalPrice();
            $pharmacyRequest->save();

            // Audit: Log pharmacy request creation
            try {
                Report::log('Medicine Requested', Report::TYPE_USER_REPORT, 'A nurse submitted a medicine request to pharmacy', [
                    'patient_id' => $pharmacyRequest->patient_id,
                    'admission_id' => $pharmacyRequest->admission_id,
                    'pharmacy_request_id' => $pharmacyRequest->id,
                    'requested_by' => auth()->id(),
                    'patient_name' => $pharmacyRequest->patient_name,
                    'item_code' => $pharmacyRequest->item_code,
                    'generic_name' => $pharmacyRequest->generic_name,
                    'brand_name' => $pharmacyRequest->brand_name,
                    'quantity' => $pharmacyRequest->quantity,
                    'unit_price' => floatval($pharmacyRequest->unit_price ?? 0),
                ]);
            } catch (\Throwable $e) {
                // Don't block the main flow if logging fails
                \Log::error('Failed to create pharmacy request audit: ' . $e->getMessage());
            }

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

            $pharmacyRequest = PharmacyRequest::findOrFail($id);
            \Log::info("Found request: {$pharmacyRequest->id}, Status: {$pharmacyRequest->status}");
            
            if ($pharmacyRequest->status !== PharmacyRequest::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be dispensed'
                ], 400);
            }

            // Check if there's enough stock
            $pharmacyStock = PharmacyStock::where('item_code', $pharmacyRequest->item_code)->first();
            
            // If not found by item_code, try to find by generic_name or brand_name
            if (!$pharmacyStock && $pharmacyRequest->generic_name) {
                $pharmacyStock = PharmacyStock::where('generic_name', $pharmacyRequest->generic_name)->first();
            }
            
            if (!$pharmacyStock && $pharmacyRequest->brand_name) {
                $pharmacyStock = PharmacyStock::where('brand_name', $pharmacyRequest->brand_name)->first();
            }
            
            if (!$pharmacyStock || $pharmacyStock->quantity < $pharmacyRequest->quantity) {
                $availableQty = $pharmacyStock ? $pharmacyStock->quantity : 0;
                $medicineName = $pharmacyRequest->generic_name ?: ($pharmacyRequest->brand_name ?: $pharmacyRequest->item_code);
                
                \Log::info("Insufficient stock - Medicine: {$medicineName}, Available: {$availableQty}, Required: {$pharmacyRequest->quantity}");
                
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$medicineName}. Available: {$availableQty} units, Required: {$pharmacyRequest->quantity} units. Please check inventory or reduce quantity."
                ], 400);
            }

            // Get patient information
            $patient = Patient::find($pharmacyRequest->patient_id);
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found'
                ], 400);
            }

            // Get current active admission
            $currentAdmission = $patient->currentAdmission;
            if (!$currentAdmission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient has no active admission. Medicine can only be dispensed to patients with active admissions.'
                ], 400);
            }

            // Update pharmacy stock
            $pharmacyStock->quantity -= $pharmacyRequest->quantity;
            $pharmacyStock->save();

            // Create patient medicine record
            $patientMedicineData = [
                'patient_id' => $pharmacyRequest->patient_id,
                'pharmacy_request_id' => $pharmacyRequest->id,
                'patient_no' => $patient->patient_no,
                'patient_name' => $pharmacyRequest->patient_name,
                'item_code' => $pharmacyRequest->item_code,
                'generic_name' => $pharmacyRequest->generic_name,
                'brand_name' => $pharmacyRequest->brand_name,
                'quantity' => $pharmacyRequest->quantity,
                'unit_price' => floatval($pharmacyRequest->unit_price ?? 0),
                'total_price' => floatval($pharmacyRequest->total_price ?? 0),
                'notes' => $pharmacyRequest->notes,
                'dispensed_by' => auth()->id(),
                'dispensed_at' => now(),
            ];
            
            $patientMedicine = PatientMedicine::create($patientMedicineData);

            // Update request status and link to admission
            $updateData = [
                'status' => PharmacyRequest::STATUS_DISPENSED,
                'dispensed_at' => now()->format('Y-m-d H:i:s'),
                'dispensed_by' => auth()->id(),
                'admission_id' => $currentAdmission->id,
                'updated_at' => now()->format('Y-m-d H:i:s')
            ];
            
            $pharmacyRequest->update($updateData);

            // Audit: Log pharmacy dispense action
            try {
                Report::log('Medicine Dispensed', Report::TYPE_USER_REPORT, 'Pharmacy dispensed medicine for a request', [
                    'patient_id' => $pharmacyRequest->patient_id,
                    'admission_id' => $pharmacyRequest->admission_id,
                    'pharmacy_request_id' => $pharmacyRequest->id,
                    'patient_medicine_id' => $patientMedicine->id,
                    'dispensed_by' => auth()->id(),
                    'dispensed_at' => now()->toDateTimeString(),
                    'quantity' => $pharmacyRequest->quantity,
                    'unit_price' => floatval($pharmacyRequest->unit_price ?? 0),
                    'total_price' => floatval($pharmacyRequest->total_price ?? 0),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create pharmacy dispense audit: ' . $e->getMessage());
            }

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

            // Audit: Log pharmacy request cancellation
            try {
                Report::log('Medicine Request Cancelled', Report::TYPE_USER_REPORT, 'A pharmacy request was cancelled', [
                    'patient_id' => $request->patient_id,
                    'admission_id' => $request->admission_id,
                    'pharmacy_request_id' => $request->id,
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => $request->cancelled_at->toDateTimeString(),
                    'status' => $request->status,
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create pharmacy cancel audit: ' . $e->getMessage());
            }

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
    public function getPatientMedicinesApi(Request $request, $patientId)
    {
        try {
            // Get patient info
            $patient = Patient::findOrFail($patientId);
            
            $query = PatientMedicine::with(['dispensedBy'])
                ->where('patient_id', $patientId);
            
            // Filter by admission if admission_id is provided - STRICT filtering  
            $admissionId = $request->query('admission_id');
            if ($admissionId) {
                // Only show medicines that are explicitly linked to this admission
                $query->whereHas('pharmacyRequest', function($subQ) use ($admissionId) {
                    $subQ->where('admission_id', '=', $admissionId);
                });
            }
            
            $medicines = $query->orderBy('dispensed_at', 'desc')->get();

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
                'medicines' => $formattedMedicines,
                'patient' => [
                    'id' => $patient->id,
                    'patient_no' => $patient->patient_no,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load patient medicines: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate that the requested medicine item exists in stocks reference masterlist
     */
    private function validateStockReference($validator, $request)
    {
    $itemCode = trim((string)($request->item_code ?? ''));
    $genericName = trim((string)($request->generic_name ?? ''));
    $brandName = trim((string)($request->brand_name ?? ''));
        
        // If no item information is provided, skip validation
        if (empty($itemCode) && empty($genericName) && empty($brandName)) {
            $validator->errors()->add('medicine', 'Please provide at least one of: Item Code, Generic Name, or Brand Name.');
            return;
        }
        
        // If item_code was provided, require an exact match on COL 1 only.
        if (!empty($itemCode)) {
            $stockItem = \App\Models\StocksReference::excludeHeader()
                ->where('COL 1', $itemCode)
                ->first();
        } else {
            // Fallback: search by generic or brand names if item_code not provided
            $stockItem = \App\Models\StocksReference::excludeHeader()
                ->where(function ($query) use ($genericName, $brandName) {
                    if (!empty($genericName)) {
                        $query->orWhere('COL 2', 'LIKE', '%' . $genericName . '%');
                    }
                    if (!empty($brandName)) {
                        $query->orWhere('COL 3', 'LIKE', '%' . $brandName . '%');
                    }
                })
                ->first();
        }
            
        if (!$stockItem) {
            $searchTerm = '';
            if (!empty($itemCode)) $searchTerm .= "Item Code: $itemCode ";
            if (!empty($genericName)) $searchTerm .= "Generic Name: $genericName ";
            if (!empty($brandName)) $searchTerm .= "Brand Name: $brandName ";
            
            $validator->errors()->add('medicine', 'The requested medicine (' . trim($searchTerm) . ') was not found in the pharmacy masterlist. Please verify the item details and try again.');
        }
        
        // If item exists, validate that the provided details match
        if ($stockItem) {
            $errors = [];

            $masterCode = trim((string)($stockItem->{'COL 1'} ?? ''));
            $masterGeneric = trim((string)($stockItem->{'COL 2'} ?? ''));
            $masterBrand = trim((string)($stockItem->{'COL 3'} ?? ''));

            // If item_code was provided and matches the masterlist code, accept immediately.
            if (!empty($itemCode) && $masterCode === $itemCode) {
                return;
            }

            // If item_code was provided but does not match, record an error.
            if (!empty($itemCode) && $masterCode !== $itemCode) {
                $errors[] = "Item Code '$itemCode' does not match masterlist (Expected: {$masterCode})";
            }

            // Only validate generic/brand if the masterlist has a non-empty value for them.
            if (!empty($genericName) && $masterGeneric !== '') {
                if (stripos($masterGeneric, $genericName) === false) {
                    $errors[] = "Generic Name '$genericName' does not match masterlist (Expected: {$masterGeneric})";
                }
            }

            if (!empty($brandName) && $masterBrand !== '') {
                if (stripos($masterBrand, $brandName) === false) {
                    $errors[] = "Brand Name '$brandName' does not match masterlist (Expected: {$masterBrand})";
                }
            }

            if (!empty($errors)) {
                // Log the stock item for debugging purposes
                \Log::warning('Stock reference mismatch', [
                    'requested' => ['item_code' => $itemCode, 'generic_name' => $genericName, 'brand_name' => $brandName],
                    'master' => ['COL1' => $masterCode, 'COL2' => $masterGeneric, 'COL3' => $masterBrand],
                    'errors' => $errors,
                ]);
                $validator->errors()->add('medicine', 'Medicine details mismatch: ' . implode(', ', $errors));
            }
        }
    }
}