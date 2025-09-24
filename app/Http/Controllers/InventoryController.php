<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockPrice;
use App\Models\StockOrder;
use App\Models\StocksReference;

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
        
        $orders = $query->get();
        
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

    public function reports()
    {
        return view('Inventory.inventory_reports');
    }

    public function account()
    {
        return view('Inventory.inventory_account');
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
        ]);

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
            $finalData = [
                'item_code' => $data['item_code'],
                'generic_name' => $data['generic_name'] ?: ($referenceStock['COL 2'] ?? null),
                'brand_name' => $data['brand_name'] ?: ($referenceStock['COL 3'] ?? null),
                'price' => $data['price'],
                'quantity' => $data['quantity'],
            ];

            // Try finding existing stock by item_code first
            $stock = StockPrice::where('item_code', $data['item_code'])->first();

            if ($stock) {
                // Update existing stock: add quantity and update price
                $stock->quantity = ($stock->quantity ?? 0) + intval($finalData['quantity']);
                $stock->price = $finalData['price'];
                
                // Update generic/brand names if they were empty before
                if (!$stock->generic_name && $finalData['generic_name']) {
                    $stock->generic_name = $finalData['generic_name'];
                }
                if (!$stock->brand_name && $finalData['brand_name']) {
                    $stock->brand_name = $finalData['brand_name'];
                }
                
                $stock->save();
                $message = 'Stock updated successfully. Added ' . $finalData['quantity'] . ' units.';
            } else {
                // Create new stock entry
                $stock = StockPrice::create($finalData);
                $message = 'New stock item created successfully with ' . $finalData['quantity'] . ' units.';
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
        ]);

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

            // Use fill to update permitted attributes
            $stock->fill($data);
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
        $stock = StocksReference::excludeHeader()->where('COL 1', $itemCode)->first();
        
        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $stock->id ?? null,
                'item_code' => $stock['COL 1'] ?? '',
                'generic_name' => $stock['COL 2'] ?? '',
                'brand_name' => $stock['COL 3'] ?? '',
                'price' => $stock['COL 4'] ?? '',
                'additional_info' => $stock['COL 5'] ?? '',
            ]
        ]);
    }
}

