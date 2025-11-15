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
        // Don't return empty if q is empty - we want to show all rooms for dropdown

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
            // If no search query, return all rooms (for dropdown), otherwise filter
            if ($q === '') {
                $rows = DB::table($table)
                    ->select(DB::raw("`$nameCol` as name"), DB::raw("`$priceCol` as price"))
                    ->limit(21) // Get one extra to account for potential header row
                    ->get();
            } else {
                $like = '%' . $q . '%';
                $rows = DB::table($table)
                    ->select(DB::raw("`$nameCol` as name"), DB::raw("`$priceCol` as price"))
                    ->where($nameCol, 'like', $like)
                    ->orWhere($priceCol, 'like', $like)
                    ->limit(21) // Get one extra to account for potential header row
                    ->get();
            }

            // Filter out potential header rows (case-insensitive check)
            $filteredRows = $rows->filter(function($r) {
                $name = strtolower(trim($r->name ?? ''));
                $price = strtolower(trim($r->price ?? ''));
                // Skip rows that look like table headers
                return !($name === 'room name' || $name === 'name' || $name === 'room' || 
                        $price === 'room price' || $price === 'price' || 
                        ($name === 'col 1' && $price === 'col 2'));
            })->take(20); // Take only 20 after filtering

            return response()->json($filteredRows->map(function($r){
                return ['name' => $r->name, 'price' => $r->price];
            })->values()->all());
        } catch (\Throwable $e) {
            Log::error('RoomController search error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Validate if a room name exists in the database.
     * Accepts POST request with 'name' parameter.
     */
    public function validate(Request $request)
    {
        $name = trim($request->input('name', ''));
        
        if (empty($name)) {
            return response()->json(['valid' => false, 'message' => 'No room name provided']);
        }

        $table = 'roomlist';
        
        try {
            if (!Schema::hasTable($table)) {
                return response()->json(['valid' => false, 'message' => 'Room table not found']);
            }

            // Determine likely column names (same logic as search method)
            $columns = Schema::getColumnListing($table);
            $candidatesName = ['room_name', 'Room Name', 'name', 'room', 'roomname'];
            $nameCol = null;
            
            foreach ($candidatesName as $cand) {
                if (in_array($cand, $columns)) {
                    $nameCol = $cand;
                    break;
                }
            }

            // Fallback to first column if nothing matched
            if (!$nameCol) {
                if (count($columns) >= 1) {
                    $nameCol = $columns[0];
                    Log::warning('RoomController validate using fallback column: ' . $nameCol);
                } else {
                    return response()->json(['valid' => false, 'message' => 'No name column found']);
                }
            }

            // Check if the room name exists in the database
            $exists = DB::table($table)
                ->where($nameCol, $name)
                ->exists();

            return response()->json(['valid' => $exists]);

        } catch (\Throwable $e) {
            Log::error('RoomController validation error: ' . $e->getMessage());
            return response()->json(['valid' => false, 'message' => 'Server error'], 500);
        }
    }
}
