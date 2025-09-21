<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockPrice;

class InventoryController extends Controller
{
    public function index()
    {
        return view('Inventory.inventory_home');
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

            // Return paginated records (15 per page)
            $stocks = $stocksQuery->orderBySemantic('generic_name')->paginate(15);

            return view('Inventory.inventory_stocks', compact('stocks', 'q'));
        } catch (\Throwable $e) {
            // Log the error and return an empty collection so the page doesn't crash.
            \Log::error('Inventory stocks load failed: ' . $e->getMessage());

            $stocks = collect([]);
            $dbError = $e->getMessage();
            return view('Inventory.inventory_stocks', compact('stocks', 'q', 'dbError'));
        }
    }

    public function orders()
    {
        return view('Inventory.inventory_orders');
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
     */
    public function addStock(Request $request)
    {
        $data = $request->validate([
            'item_code' => ['nullable', 'string', 'max:100'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Require either item_code or generic_name
        if (empty($data['item_code']) && empty($data['generic_name'])) {
            return response()->json(['ok' => false, 'message' => 'Please provide item code or generic name.'], 422);
        }

        // Try finding existing stock by item_code first, then by generic+brand
        $stock = null;
        if (!empty($data['item_code'])) {
            $stock = StockPrice::whereSemantic('item_code', $data['item_code'])->first();
        }

        if (!$stock && !empty($data['generic_name'])) {
            $q = StockPrice::whereSemantic('generic_name', $data['generic_name']);
            if (!empty($data['brand_name'])) {
                $q->whereSemantic('brand_name', $data['brand_name']);
            }
            $stock = $q->first();
        }

        if ($stock) {
            $stock->quantity = ($stock->quantity ?? 0) + intval($data['quantity']);
            if (isset($data['price'])) { $stock->price = $data['price']; }
            $stock->save();
        } else {
            $stock = StockPrice::create([
                'item_code' => $data['item_code'] ?? null,
                'generic_name' => $data['generic_name'] ?? null,
                'brand_name' => $data['brand_name'] ?? null,
                'price' => $data['price'] ?? 0,
                'quantity' => $data['quantity'],
            ]);
        }

        return response()->json(['ok' => true, 'stock' => $stock]);
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

        $matches = StockPrice::whereSemanticLike('item_code', $q)
                    ->orWhere(function($query) use ($q) {
                        $query->whereSemanticLike('generic_name', $q);
                    })
                    ->orderBySemantic('generic_name')
                    ->limit(15)
                    ->get();

        // Map to readable properties using accessors
        $results = $matches->map(function($m){
            return [
                'id' => $m->id,
                'item_code' => $m->item_code,
                'generic_name' => $m->generic_name,
                'brand_name' => $m->brand_name,
                'price' => $m->price,
                'quantity' => $m->quantity,
            ];
        });

        return response()->json($results);
    }
}
