<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    /**
     * Search rooms by name or price (AJAX)
     * Returns JSON array of {name, price}
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $table = 'roomlist';
        if (!Schema::hasTable($table)) {
            Log::warning('Room search requested but table not found: ' . $table);
            return response()->json([]);
        }

        // determine likely column names
        $columns = Schema::getColumnListing($table);
        $candidatesName = ['room_name', 'Room Name', 'name', 'room', 'roomname'];
        $candidatesPrice = ['room_price', 'Room Price', 'price', 'roomprice'];
        $nameCol = null; $priceCol = null;
        foreach ($candidatesName as $cand) { if (in_array($cand, $columns)) { $nameCol = $cand; break; } }
        foreach ($candidatesPrice as $cand) { if (in_array($cand, $columns)) { $priceCol = $cand; break; } }

        // fallback to first two columns if nothing matched
        if (!$nameCol || !$priceCol) {
            if (count($columns) >= 2) {
                $nameCol = $nameCol ?: $columns[0];
                $priceCol = $priceCol ?: $columns[1];
                Log::warning('RoomController using fallback columns: ' . $nameCol . ', ' . $priceCol);
            }
        }

        if (!$nameCol) $nameCol = $columns[0] ?? 'name';
        if (!$priceCol) $priceCol = $columns[1] ?? 'price';

        try {
            $like = '%' . $q . '%';
            $rows = DB::table($table)
                ->select(DB::raw("`$nameCol` as name"), DB::raw("`$priceCol` as price"))
                ->where($nameCol, 'like', $like)
                ->orWhere($priceCol, 'like', $like)
                ->limit(20)
                ->get();

            return response()->json($rows->map(function($r){
                return ['name' => $r->name, 'price' => $r->price];
            })->all());
        } catch (\Throwable $e) {
            Log::error('RoomController search error: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}
