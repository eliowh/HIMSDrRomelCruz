<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProcedureController extends Controller
{
    /**
     * AJAX search for procedure names based on category.
     * Accepts ?q=term&category=type and returns up to 15 matches as JSON.
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $category = $request->query('category', '');
        $q = trim($q);

        if ($q === '' || $category === '') {
            return response()->json([]);
        }

        // Map categories to table names
        $tableMap = [
            'xray' => 'xray_prices',
            'ultrasound' => 'ultrasound_prices',
            'laboratory' => 'laboratory_prices'
        ];

        if (!isset($tableMap[$category])) {
            return response()->json(['error' => 'invalid_category'], 400);
        }

        $table = $tableMap[$category];

        try {
            if (!Schema::hasTable($table)) {
                // Table missing — return empty array
                return response()->json([]);
            }

            $columns = Schema::getColumnListing($table);
            $lower = array_map('strtolower', $columns);

            // Resolve name/procedure column (looking for first column which should be the procedure name)
            $nameColumn = null;
            foreach (['name', 'procedure', 'test', 'examination', 'service'] as $cand) {
                $pos = array_search($cand, $lower, true);
                if ($pos !== false) {
                    $nameColumn = $columns[$pos];
                    break;
                }
            }
            
            // If no specific name column found, use the first column
            if ($nameColumn === null && count($columns) > 0) {
                $nameColumn = $columns[0];
                Log::info("Procedure search: using first column as name column for table {$table}", ['column' => $nameColumn]);
            }

            if ($nameColumn === null) {
                Log::warning("Procedure search: couldn't resolve name column for table {$table}", ['columns' => $columns]);
                return response()->json([]);
            }

            $rows = DB::table($table)
                ->select(DB::raw("`{$nameColumn}` as name"))
                ->where($nameColumn, 'like', "%{$q}%")
                ->where($nameColumn, '!=', 'Laboratory')  // Skip header rows
                ->where($nameColumn, '!=', 'Ultrasound')
                ->where($nameColumn, '!=', 'X-ray')
                ->where($nameColumn, '!=', 'Price')
                ->limit(15)
                ->get();

            return response()->json($rows);

        } catch (QueryException $e) {
            Log::error('Procedure search query failed', ['exception' => $e->getMessage(), 'table' => $table]);
            return response()->json(['error' => 'database_error', 'message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('Procedure search unexpected error', ['exception' => $e->getMessage(), 'table' => $table]);
            return response()->json(['error' => 'server_error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all procedures for a specific category
     */
    public function getByCategory(Request $request)
    {
        $category = $request->query('category', '');

        if ($category === '') {
            return response()->json(['error' => 'category_required'], 400);
        }

        // Map categories to table names
        $tableMap = [
            'xray' => 'xray_prices',
            'ultrasound' => 'ultrasound_prices',
            'laboratory' => 'laboratory_prices'
        ];

        if (!isset($tableMap[$category])) {
            return response()->json(['error' => 'invalid_category'], 400);
        }

        $table = $tableMap[$category];

        try {
            if (!Schema::hasTable($table)) {
                return response()->json([]);
            }

            $columns = Schema::getColumnListing($table);
            
            // For these specific pricing tables, we know the structure:
            // COL 1 = procedure name, COL 2 = price
            $nameColumn = 'COL 1';
            $priceColumn = 'COL 2';
            
            // Check if the expected columns exist
            if (!in_array($nameColumn, $columns) || !in_array($priceColumn, $columns)) {
                // Fallback to dynamic detection if standard columns don't exist
                $nameColumns = ['procedure_name', 'test_name', 'name', 'procedure', 'test'];
                $priceColumns = ['price', 'procedure_price', 'test_price', 'cost'];
                
                $nameColumn = null;
                $priceColumn = null;
                
                foreach ($nameColumns as $col) {
                    if (in_array($col, $columns)) {
                        $nameColumn = $col;
                        break;
                    }
                }
                
                foreach ($priceColumns as $col) {
                    if (in_array($col, $columns)) {
                        $priceColumn = $col;
                        break;
                    }
                }
                
                // Default to first column if no specific name column found
                if (!$nameColumn) {
                    $nameColumn = $columns[0] ?? 'name';
                }
            }

            $query = DB::table($table)
                ->select(
                    DB::raw("`{$nameColumn}` as name"),
                    DB::raw("`{$priceColumn}` as price")
                )
                ->where($nameColumn, '!=', 'Laboratory')  // Skip header rows
                ->where($nameColumn, '!=', 'Ultrasound')
                ->where($nameColumn, '!=', 'X-ray')
                ->where($nameColumn, '!=', 'Price')
                ->whereNotNull($nameColumn)
                ->where($nameColumn, '!=', '')
                ->orderBy($nameColumn);

            $rows = $query->get();

            // Clean and format the price data
            $rows = $rows->map(function($row) {
                $item = (array) $row;
                
                // Clean price field - remove commas and convert to float
                $price = isset($item['price']) ? $item['price'] : '0';
                $price = str_replace(',', '', $price); // Remove commas
                $price = preg_replace('/[^0-9.]/', '', $price); // Remove non-numeric characters except decimal
                $item['price'] = (float) $price;
                
                return $item;
            });

            return response()->json($rows);

        } catch (QueryException $e) {
            Log::error('Get procedures by category query failed', ['exception' => $e->getMessage(), 'table' => $table]);
            return response()->json(['error' => 'database_error', 'message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('Get procedures by category unexpected error', ['exception' => $e->getMessage(), 'table' => $table]);
            return response()->json(['error' => 'server_error', 'message' => $e->getMessage()], 500);
        }
    }
}