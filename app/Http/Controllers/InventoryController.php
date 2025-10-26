<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockPrice;
use App\Models\StockOrder;
use App\Models\StocksReference;
use App\Models\PharmacyStock;
use App\Models\Report;

class InventoryController extends Controller
{
    public function index()
    {
        try {
            // Dashboard statistics
            $totalStocks = StockPrice::count();
            $lowStockCount = StockPrice::where('quantity', '<=', 10)->count();
            $outOfStockCount = StockPrice::where('quantity', '<=', 0)->count();
            $totalOrders = StockOrder::count();
            $pendingOrders = StockOrder::where('status', 'pending')->count();
            $completedOrders = StockOrder::where('status', 'completed')->count();
            
            // Recent stock additions (last 7 days if timestamps available)
            $recentStocks = StockPrice::orderBy('id', 'desc')->limit(5)->get();
            
            // Recent orders
            $recentOrders = StockOrder::with('user')
                ->orderBy('requested_at', 'desc')
                ->limit(5)
                ->get();
                
            // Stock value calculation (total inventory value)
            $totalStockValue = StockPrice::selectRaw('SUM(quantity * price) as total_value')
                ->value('total_value') ?? 0;
            
            $dashboardData = [
                'totalStocks' => $totalStocks,
                'lowStockCount' => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'completedOrders' => $completedOrders,
                'recentStocks' => $recentStocks,
                'recentOrders' => $recentOrders,
                'totalStockValue' => number_format($totalStockValue, 2)
            ];
            
            return view('Inventory.inventory_home', $dashboardData);
        } catch (\Exception $e) {
            \Log::error('Dashboard data error: ' . $e->getMessage());
            
            // Return view with empty data if error occurs
            $dashboardData = [
                'totalStocks' => 0,
                'lowStockCount' => 0,
                'outOfStockCount' => 0,
                'totalOrders' => 0,
                'pendingOrders' => 0,
                'completedOrders' => 0,
                'recentStocks' => collect(),
                'recentOrders' => collect(),
                'totalStockValue' => '0.00'
            ];
            
            return view('Inventory.inventory_home', $dashboardData);
        }
    }

    public function stocks()
    {
        $q = request()->get('q', '');

        try {
            $stocksQuery = StockPrice::query();
            if ($q) {
                $stocksQuery->where(function($builder) use ($q) {
                    $builder->where('item_code', 'like', "%{$q}%")
                            ->orWhere('generic_name', 'like', "%{$q}%")
                            ->orWhere('brand_name', 'like', "%{$q}%");
                });
            }

            // Return paginated records and normalize null quantities to 0
            $stocks = $stocksQuery->orderBy('generic_name')->paginate(15);
            
            // Transform the paginated data to normalize quantities
            $stocks->getCollection()->transform(function($s) {
                $s->quantity = $s->quantity ?? 0;
                return $s;
            });

            return view('Inventory.inventory_stocks', compact('stocks', 'q'));
        } catch (\Throwable $e) {
            // Log the error and return an empty collection so the page doesn't crash.
            \Log::error('Inventory stocks load failed: ' . $e->getMessage());

            // Create a paginator from an empty array
            $stocks = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            $dbError = $e->getMessage();
            return view('Inventory.inventory_stocks', compact('stocks', 'q', 'dbError'));
        }
    }

    public function orders(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = StockOrder::with('user')
            ->orderBy('requested_at', 'desc');
        
        // Filter by status if specified
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Paginate by 20 items
        $orders = $query->paginate(20);
        
        // Preserve the status filter in pagination links
        $orders->appends(['status' => $status]);
        
        // Get counts for each status
        $statusCounts = [
            'all' => StockOrder::count(),
            'pending' => StockOrder::where('status', 'pending')->count(),
            'approved' => StockOrder::where('status', 'approved')->count(),
            'completed' => StockOrder::where('status', 'completed')->count(),
            'cancelled' => StockOrder::where('status', 'cancelled')->count(),
        ];
        
        return view('Inventory.inventory_orders', compact('orders', 'statusCounts', 'status'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,completed,cancelled'
        ]);

        try {
            $order = StockOrder::findOrFail($id);
            
            // Validate status transition
            $validTransitions = [
                'pending' => ['approved', 'cancelled'],
                'approved' => ['completed', 'cancelled'],
                'completed' => [], // No transitions from completed
                'cancelled' => [] // No transitions from cancelled
            ];

            if (!in_array($request->status, $validTransitions[$order->status] ?? [])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition'
                ], 400);
            }

            $order->status = $request->status;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reports(Request $request)
    {
        try {
            $reportType = $request->get('type', 'overview');
            
            // Base query
            $stocksQuery = StockPrice::query();
            
            switch ($reportType) {
                case 'low-stock':
                    $data = $stocksQuery->lowStock()
                        ->inStock()
                        ->orderBy('quantity', 'asc')
                        ->paginate(20);
                    break;
                    
                case 'out-of-stock':
                    $data = $stocksQuery->outOfStock()
                        ->orderBy('generic_name')
                        ->paginate(20);
                    break;
                    
                case 'expiring':
                    $days = (int) $request->get('days', 30);
                    $data = $stocksQuery->expiringSoon($days)
                        ->inStock()
                        ->orderBy('expiry_date', 'asc')
                        ->paginate(20);
                    break;
                    
                case 'expired':
                    $data = $stocksQuery->expired()
                        ->orderBy('expiry_date', 'desc')
                        ->paginate(20);
                    break;
                    
                case 'stock-movement':
                    // Get recent stock orders for movement analysis
                    $data = StockOrder::with('user')
                        ->where('status', 'completed')
                        ->where('requested_at', '>=', now()->subDays(30))
                        ->orderBy('requested_at', 'desc')
                        ->paginate(20);
                    break;
                    
                case 'overview':
                default:
                    // Overview doesn't need pagination - just summary stats
                    $data = null;
                    break;
            }
            
            // Calculate summary statistics
            $stats = [
                'total_items' => StockPrice::count(),
                'total_value' => StockPrice::selectRaw('SUM(quantity * price) as total')->value('total') ?? 0,
                'in_stock' => StockPrice::inStock()->count(),
                'out_of_stock' => StockPrice::outOfStock()->count(),
                'low_stock' => StockPrice::lowStock()->inStock()->count(),
                'expiring_soon' => StockPrice::expiringSoon((int) 30)->inStock()->count(),
                'expired' => StockPrice::expired()->count(),
                'recent_orders' => StockOrder::where('requested_at', '>=', now()->subDays(7))->count(),
            ];
            
            // Get top 10 most valuable items for overview
            $topValueItems = StockPrice::selectRaw('*, (quantity * price) as total_value')
                ->where('quantity', '>', 0)
                ->orderBy('total_value', 'desc')
                ->limit(10)
                ->get();
                
            // Get recent low stock alerts
            $lowStockAlerts = StockPrice::lowStock()
                ->inStock()
                ->orderBy('quantity', 'asc')
                ->limit(5)
                ->get();
                
            // Get upcoming expiries
            $upcomingExpiries = StockPrice::expiringSoon((int) 15)
                ->inStock()
                ->orderBy('expiry_date', 'asc')
                ->limit(5)
                ->get();
            
            return view('Inventory.inventory_reports', compact(
                'reportType', 
                'data', 
                'stats', 
                'topValueItems', 
                'lowStockAlerts', 
                'upcomingExpiries'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Inventory reports error: ' . $e->getMessage());
            
            // Return basic view with error
            $stats = [
                'total_items' => 0,
                'total_value' => 0,
                'in_stock' => 0,
                'out_of_stock' => 0,
                'low_stock' => 0,
                'expiring_soon' => 0,
                'expired' => 0,
                'recent_orders' => 0,
            ];
            
            return view('Inventory.inventory_reports', compact('stats'))
                ->with('error', 'Error loading reports data.');
        }
    }

    /**
     * Add quantity to an existing stock item or create a new stock entry.
     * Now uses stocks reference data for validation and defaults.
     */
    public function addStock(Request $request)
    {
        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:100'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'date_received' => ['nullable', 'date'],
            'non_perishable' => ['nullable', 'string'],
        ]);

        // If non-perishable is checked, expiry date should be null
        if ($request->has('non_perishable') && $request->non_perishable === 'on') {
            $data['expiry_date'] = null;
        }

        try {
            // First, verify the item exists in stocks reference
            $referenceStock = StocksReference::excludeHeader()
                ->where('COL 1', $data['item_code'])
                ->first();

            if (!$referenceStock) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Item code not found in reference database. Please verify the item code.'
                ], 422);
            }

            // Use reference data as defaults if not provided
            $finalData = array_merge($data, [
                'generic_name' => $data['generic_name'] ?: ($referenceStock['COL 2'] ?? null),
                'brand_name' => $data['brand_name'] ?: ($referenceStock['COL 3'] ?? null),
            ]);

            // Try finding existing stock by item_code first
            $stock = StockPrice::where('item_code', $data['item_code'])->first();

            if ($stock) {
                // Update existing stock: add quantity and update other fields
                $stock->quantity = ($stock->quantity ?? 0) + intval($finalData['quantity']);
                $stock->price = $finalData['price'];
                $stock->reorder_level = $finalData['reorder_level'] ?? $stock->reorder_level;
                $stock->expiry_date = $finalData['expiry_date'] ?? $stock->expiry_date;
                $stock->supplier = $finalData['supplier'] ?? $stock->supplier;
                $stock->batch_number = $finalData['batch_number'] ?? $stock->batch_number;
                $stock->date_received = $finalData['date_received'] ?? $stock->date_received;
                
                // Update generic/brand names if they were empty before
                if (!$stock->generic_name && $finalData['generic_name']) {
                    $stock->generic_name = $finalData['generic_name'];
                }
                if (!$stock->brand_name && $finalData['brand_name']) {
                    $stock->brand_name = $finalData['brand_name'];
                }
                
                $stock->save();
                $message = 'Stock updated successfully. Added ' . $finalData['quantity'] . ' units.';
                
                // Note: Manual inventory stock additions are now separate from pharmacy stocks
                \Log::info("Manual inventory stock added/updated for item: {$finalData['item_code']} with quantity: {$finalData['quantity']} - NOT auto-transferred to pharmacy");

                try {
                    Report::log('Stock Updated', Report::TYPE_USER_REPORT, 'Existing stock updated', [
                        'stock_id' => $stock->id,
                        'item_code' => $stock->item_code,
                        'added_quantity' => $finalData['quantity'],
                        'new_total' => $stock->quantity,
                        'updated_by' => auth()->id(),
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to write stock updated audit: ' . $e->getMessage());
                }
            } else {
                // Create new stock entry
                $stock = StockPrice::create($finalData);
                $message = 'New stock item created successfully with ' . $finalData['quantity'] . ' units.';
                
                // Note: Manual inventory stock additions are now separate from pharmacy stocks
                \Log::info("New inventory stock created for item: {$finalData['item_code']} with quantity: {$finalData['quantity']} - NOT auto-transferred to pharmacy");

                try {
                    Report::log('Stock Added', Report::TYPE_USER_REPORT, 'New stock item created', [
                        'stock_id' => $stock->id,
                        'item_code' => $stock->item_code,
                        'quantity' => $stock->quantity,
                        'price' => $stock->price,
                        'created_by' => auth()->id(),
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to write stock created audit: ' . $e->getMessage());
                }
            }

            return response()->json([
                'ok' => true, 
                'stock' => $stock,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Add stock error: ' . $e->getMessage());
            return response()->json([
                'ok' => false, 
                'message' => 'Failed to add stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a stock item by id (or item_code fallback).
     */
    public function deleteStock(Request $request, $id)
    {
        try {
            // Try find by primary key id first
            $stock = StockPrice::find($id);

            // If not found and $id is not numeric, try by item_code
            if (!$stock) {
                $stock = StockPrice::where('item_code', $id)->first();
            }

            if (!$stock) {
                return response()->json(['ok' => false, 'message' => 'Stock item not found.'], 404);
            }

            $stock->delete();

            try {
                Report::log('Stock Deleted', Report::TYPE_USER_REPORT, 'Stock item deleted', [
                    'stock_id' => $stock->id ?? null,
                    'item_code' => $stock->item_code ?? null,
                    'deleted_by' => auth()->id(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to write stock deleted audit: ' . $e->getMessage());
            }

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \Log::error('Failed to delete stock: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Delete failed.'], 500);
        }
    }

    /**
     * Search stocks by item_code or generic_name for autocomplete.
     * Returns an array of matches with available brands grouped per generic.
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (empty($q)) {
            return response()->json([]);
        }

        $matches = StockPrice::where('item_code', 'like', "%{$q}%")
                    ->orWhere('generic_name', 'like', "%{$q}%")
                    ->orderBy('generic_name')
                    ->limit(15)
                    ->get(['id','item_code','generic_name','brand_name','price','quantity']);

        // Group brands per item (by generic_name + item_code)
        $results = $matches->map(function($m){
            return [
                'id' => $m->id,
                'item_code' => $m->item_code,
                'generic_name' => $m->generic_name,
                'brand_name' => $m->brand_name,
                'price' => $m->price,
                'quantity' => $m->quantity ?? 0,
            ];
        });

        return response()->json($results);
    }
    
    /**
     * Update stock item details (item_code, generic_name, brand_name, price)
     */
    public function updateStock(Request $request, $id)
    {
        // Log incoming request for debugging (include DB name for clarity)
        \Log::info('Inventory.updateStock called', ['id' => $id, 'payload' => $request->all(), 'db' => \DB::connection()->getDatabaseName()]);

        $data = $request->validate([
            'item_code' => ['nullable','string','max:100'],
            'generic_name' => ['nullable','string','max:255'],
            'brand_name' => ['nullable','string','max:255'],
            'price' => ['nullable','numeric','min:0'],
            'quantity' => ['nullable','integer','min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'date_received' => ['nullable', 'date'],
            'non_perishable' => ['nullable', 'string'],
        ]);

        // If non-perishable is checked, expiry date should be null
        if ($request->has('non_perishable') && $request->non_perishable === 'on') {
            $data['expiry_date'] = null;
        }

        // Try several lookup strategies so edits work regardless of whether 'id' is numeric PK or an item_code
        $stock = null;
        try {
            $stock = StockPrice::find($id);
            if (!$stock) {
                $stock = StockPrice::where('item_code', $id)->first();
            }

            // If still not found but payload contains item_code, try that
            if (!$stock && !empty($data['item_code'])) {
                $stock = StockPrice::where('item_code', $data['item_code'])->first();
            }

            if (!$stock) {
                \Log::warning('Inventory.updateStock: stock not found', ['id'=>$id, 'payload'=>$data]);
                return response()->json(['ok' => false, 'message' => 'Stock item not found.'], 404);
            }

            \Log::info('Inventory.updateStock found stock', ['stock_before' => $stock->toArray()]);

            // Update only fields that have values, preserve existing values for empty fields
            foreach ($data as $key => $value) {
                if ($key === 'expiry_date' || $key === 'date_received') {
                    // Only update date fields if they have a value, otherwise keep existing
                    if (!empty($value)) {
                        $stock->$key = $value;
                    }
                } else {
                    // For other fields, update normally (empty strings are allowed)
                    $stock->$key = $value;
                }
            }
            $stock->save();

            // Refresh from DB (in case of casts/defaults)
            $stock = $stock->fresh();

            \Log::info('Inventory.updateStock saved stock', ['stock_after' => $stock->toArray()]);

            return response()->json(['ok' => true, 'stock' => $stock]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \Log::warning('Inventory.updateStock validation failed', ['errors' => $ve->errors(), 'payload' => $request->all()]);
            return response()->json(['ok' => false, 'errors' => $ve->errors()], 422);
        } catch (\Throwable $e) {
            \Log::error('Inventory.updateStock error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['ok' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    // API endpoints for stocks reference (similar to pharmacy controller)
    public function getStocksReference(Request $request)
    {
        $search = $request->get('search', '');
        $type = $request->get('type', 'all'); // 'item_code', 'generic_name', 'brand_name', or 'all'
        
        $query = StocksReference::excludeHeader();
        
        if ($search) {
            switch ($type) {
                case 'item_code':
                    $query->where('COL 1', 'like', '%' . $search . '%');
                    break;
                case 'generic_name':
                    $query->hasGenericName()
                          ->where('COL 2', 'like', '%' . $search . '%');
                    break;
                case 'brand_name':
                    $query->hasBrandName()
                          ->where('COL 3', 'like', '%' . $search . '%');
                    break;
                default:
                    $query->where(function($q) use ($search) {
                        $q->where('COL 1', 'like', '%' . $search . '%')
                          ->orWhere(function($subQ) use ($search) {
                              $subQ->where('COL 2', '!=', '')
                                   ->whereNotNull('COL 2')
                                   ->where('COL 2', 'like', '%' . $search . '%');
                          })
                          ->orWhere(function($subQ) use ($search) {
                              $subQ->where('COL 3', '!=', '')
                                   ->whereNotNull('COL 3')
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
                    'id' => $stock->id ?? null,
                    'item_code' => $stock['COL 1'] ?? '',
                    'generic_name' => $stock['COL 2'] ?? '',
                    'brand_name' => $stock['COL 3'] ?? '',
                    'price' => $stock['COL 4'] ?? '',
                    'additional_info' => $stock['COL 5'] ?? '',
                ];
            })
        ]);
    }

    public function getStockByItemCode($itemCode)
    {
        $stock = StockPrice::where('item_code', $itemCode)->first();
        
        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in inventory'
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
                'quantity' => $stock->quantity,
            ]
        ]);
    }

    /**
     * Process/encode an approved pharmacy order by deducting from existing stock.
     */
    public function addStockFromOrder(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:stock_orders,id'],
            'item_code' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            // Find the existing stock
            $stock = StockPrice::where('item_code', $data['item_code'])->first();

            if (!$stock) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Stock item not found in inventory.'
                ], 404);
            }

            // Check if there's enough stock
            if ($stock->quantity < $data['quantity']) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Insufficient stock. Available: ' . $stock->quantity . ', Requested: ' . $data['quantity']
                ], 400);
            }

            // Deduct the requested quantity from inventory
            $stock->quantity -= intval($data['quantity']);
            $stock->save();

            // Add the deducted quantity to pharmacy stocks
            \Log::info("Processing pharmacy order - transferring {$data['quantity']} units of {$data['item_code']} to pharmacy stocks");
            $transferData = [
                'item_code' => $stock->item_code,
                'generic_name' => $stock->generic_name,
                'brand_name' => $stock->brand_name,
                'price' => $stock->price,
                'quantity' => $data['quantity'], // The amount being transferred
                'expiry_date' => $stock->expiry_date,
                'reorder_level' => $stock->reorder_level,
                'supplier' => $stock->supplier,
                'batch_number' => $stock->batch_number,
                'date_received' => $stock->date_received,
            ];
            $this->addToPharmacyStocks($transferData);

            // Update the order status to completed
            $order = StockOrder::find($data['order_id']);
            if ($order) {
                $order->status = 'completed';
                $order->save();
            }

            // Audit: Inventory transfer to pharmacy
            try {
                Report::log('Inventory Transfer to Pharmacy', Report::TYPE_USER_REPORT, 'Inventory item transferred to pharmacy stocks', [
                    'order_id' => $data['order_id'],
                    'stock_id' => $stock->id,
                    'item_code' => $stock->item_code,
                    'quantity_transferred' => $data['quantity'],
                    'remaining_inventory' => $stock->quantity,
                    'processed_by' => auth()->id(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to write inventory transfer audit: ' . $e->getMessage());
            }

            $message = 'Order #' . $data['order_id'] . ' processed successfully. Deducted ' . $data['quantity'] . ' units from stock. Remaining: ' . $stock->quantity;

            return response()->json([
                'ok' => true, 
                'stock' => $stock,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Process pharmacy order error: ' . $e->getMessage());
            return response()->json([
                'ok' => false, 
                'message' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add encoded inventory items to pharmacy stocks
     * If item exists, add to quantity. If not, create new entry.
     */
    private function addToPharmacyStocks($stockData)
    {
        try {
            \Log::info("addToPharmacyStocks called with data: " . json_encode($stockData));

            // Check if item already exists in pharmacy stocks
            $pharmacyStock = PharmacyStock::where('item_code', $stockData['item_code'])->first();

            if ($pharmacyStock) {
                $oldQuantity = $pharmacyStock->quantity ?? 0;
                $addQuantity = intval($stockData['quantity']);
                $newQuantity = $oldQuantity + $addQuantity;

                \Log::info("Updating existing pharmacy stock {$stockData['item_code']}: {$oldQuantity} + {$addQuantity} = {$newQuantity}");

                // Add to existing quantity in pharmacy stocks
                $pharmacyStock->quantity = $newQuantity;

                // Update other fields with new data (prices, dates, etc.)
                $pharmacyStock->price = $stockData['price'];
                $pharmacyStock->expiry_date = $stockData['expiry_date'] ?? $pharmacyStock->expiry_date;
                $pharmacyStock->supplier = $stockData['supplier'] ?? $pharmacyStock->supplier;
                $pharmacyStock->batch_number = $stockData['batch_number'] ?? $pharmacyStock->batch_number;
                $pharmacyStock->date_received = $stockData['date_received'] ?? $pharmacyStock->date_received;
                $pharmacyStock->reorder_level = $stockData['reorder_level'] ?? $pharmacyStock->reorder_level;

                $saveResult = $pharmacyStock->save();
                \Log::info("Save result: " . ($saveResult ? 'SUCCESS' : 'FAILED') . " - Final quantity: {$pharmacyStock->quantity}");

                // Audit: Pharmacy stock updated
                try {
                    Report::log('Pharmacy Stock Updated', Report::TYPE_USER_REPORT, 'Existing pharmacy stock increased', [
                        'pharmacy_stock_id' => $pharmacyStock->id,
                        'item_code' => $pharmacyStock->item_code,
                        'added_quantity' => $addQuantity,
                        'new_total' => $pharmacyStock->quantity,
                        'updated_by' => auth()->id(),
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to write pharmacy stock updated audit: ' . $e->getMessage());
                }

            } else {
                \Log::info("Creating new pharmacy stock entry for {$stockData['item_code']} with quantity: {$stockData['quantity']}");

                // Create new pharmacy stock entry
                $newStock = PharmacyStock::create([
                    'item_code' => $stockData['item_code'],
                    'generic_name' => $stockData['generic_name'],
                    'brand_name' => $stockData['brand_name'],
                    'price' => $stockData['price'],
                    'quantity' => $stockData['quantity'],
                    'expiry_date' => $stockData['expiry_date'],
                    'reorder_level' => $stockData['reorder_level'] ?? 10,
                    'supplier' => $stockData['supplier'],
                    'batch_number' => $stockData['batch_number'],
                    'date_received' => $stockData['date_received'],
                ]);

                \Log::info("Created new pharmacy stock with ID: {$newStock->id} and quantity: {$newStock->quantity}");

                // Audit: Pharmacy stock created
                try {
                    Report::log('Pharmacy Stock Added', Report::TYPE_USER_REPORT, 'New pharmacy stock created from inventory transfer', [
                        'pharmacy_stock_id' => $newStock->id,
                        'item_code' => $newStock->item_code,
                        'quantity' => $newStock->quantity,
                        'created_by' => auth()->id(),
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to write pharmacy stock created audit: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to add item {$stockData['item_code']} to pharmacy stocks: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            // Don't throw exception here to avoid breaking the main inventory process
        }
    }
}

