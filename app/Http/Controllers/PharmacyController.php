<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\StockOrder;
use App\Models\StocksReference;

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
}
