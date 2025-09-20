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
            
            // Use first column as the procedure name
            $nameColumn = $columns[0] ?? 'name';

            $rows = DB::table($table)
                ->select(DB::raw("`{$nameColumn}` as name"))
                ->where($nameColumn, '!=', 'Laboratory')  // Skip header rows
                ->where($nameColumn, '!=', 'Ultrasound')
                ->where($nameColumn, '!=', 'X-ray')
                ->where($nameColumn, '!=', 'Price')
                ->orderBy($nameColumn)
                ->get();

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