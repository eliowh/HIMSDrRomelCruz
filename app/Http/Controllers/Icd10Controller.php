<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class Icd10Controller extends Controller
{
    /**
     * AJAX search for ICD10 codes/descriptions.
     * Accepts ?q=term and returns up to 15 matches as JSON.
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $q = trim($q);

        // Don't return empty if q is empty - we want to show all codes for dropdown

        $table = 'icd10namepricerate';

        try {
            if (!Schema::hasTable($table)) {
                // Table missing — return empty array (frontend will show no suggestions)
                return response()->json([]);
            }

            $columns = Schema::getColumnListing($table);
            $lower = array_map('strtolower', $columns);

            // Resolve code column
            $codeColumn = null;
            foreach (['code', 'icd', 'icd10', 'diagnosis_code', 'icd_code'] as $cand) {
                $pos = array_search($cand, $lower, true);
                if ($pos !== false) {
                    $codeColumn = $columns[$pos];
                    break;
                }
            }
            if ($codeColumn === null) {
                // fallback: any column name containing 'code'
                foreach ($lower as $i => $c) {
                    if (strpos($c, 'code') !== false) {
                        $codeColumn = $columns[$i];
                        break;
                    }
                }
            }

            // Resolve description column
            $descColumn = null;
            foreach (['description', 'desc', 'name', 'diagnosis', 'disease'] as $cand) {
                $pos = array_search($cand, $lower, true);
                if ($pos !== false) {
                    $descColumn = $columns[$pos];
                    break;
                }
            }
            if ($descColumn === null) {
                // fallback: any column name containing 'desc' or 'name'
                foreach ($lower as $i => $c) {
                    if (strpos($c, 'desc') !== false || strpos($c, 'name') !== false) {
                        $descColumn = $columns[$i];
                        break;
                    }
                }
            }

            // If we couldn't resolve both, fallback to first two columns (some datasets have odd names like 'COL 1')
            if ($codeColumn === null || $descColumn === null) {
                if (count($columns) >= 2) {
                    $codeColumn = $columns[0];
                    $descColumn = $columns[1];
                    Log::warning("ICD10 search: falling back to first two columns for table {$table}", ['columns' => $columns]);
                } else {
                    Log::warning("ICD10 search: couldn't resolve columns for table {$table}", ['columns' => $columns]);
                    return response()->json([]);
                }
            }

            // If no search query, return all codes (for dropdown), otherwise filter
            if ($q === '') {
                $rows = DB::table($table)
                    ->select(DB::raw("`{$codeColumn}` as code"), DB::raw("`{$descColumn}` as description"))
                    ->limit(51) // Get extra to account for potential header row (50 + 1)
                    ->get();
            } else {
                $rows = DB::table($table)
                    ->select(DB::raw("`{$codeColumn}` as code"), DB::raw("`{$descColumn}` as description"))
                    ->where($codeColumn, 'like', "%{$q}%")
                    ->orWhere($descColumn, 'like', "%{$q}%")
                    ->limit(16) // Get one extra to account for potential header row
                    ->get();
            }

            // Filter out potential header rows (case-insensitive check)
            $filteredRows = $rows->filter(function($r) {
                $code = strtolower(trim($r->code ?? ''));
                $description = strtolower(trim($r->description ?? ''));
                
                // Skip rows that look like table headers or column names
                return !($code === 'code' || $code === 'icd' || $code === 'icd10' || 
                        $code === 'diagnosis_code' || $code === 'icd_code' || $code === 'col 1' ||
                        $description === 'description' || $description === 'desc' || 
                        $description === 'name' || $description === 'diagnosis' || 
                        $description === 'disease' || $description === 'col 2' ||
                        ($code === 'col 1' && $description === 'col 2'));
            })->take($q === '' ? 50 : 15); // Take 50 for dropdown, 15 for search

            return response()->json($filteredRows->values()->all());

        } catch (QueryException $e) {
            // Return JSON error so frontend doesn't try to parse HTML
            Log::error('ICD10 search query failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'database_error', 'message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            Log::error('ICD10 search unexpected error', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'server_error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate if an ICD-10 code exists in the database.
     * Accepts POST request with 'code' parameter.
     */
    public function validate(Request $request)
    {
        $code = trim($request->input('code', ''));
        
        if (empty($code)) {
            return response()->json(['valid' => false, 'message' => 'No code provided']);
        }

        $table = 'icd10namepricerate';

        try {
            if (!Schema::hasTable($table)) {
                return response()->json(['valid' => false, 'message' => 'Table not found']);
            }

            $columns = Schema::getColumnListing($table);
            $lower = array_map('strtolower', $columns);

            // Resolve code column (same logic as search method)
            $codeColumn = null;
            foreach (['code', 'icd', 'icd10', 'diagnosis_code', 'icd_code'] as $cand) {
                $pos = array_search($cand, $lower, true);
                if ($pos !== false) {
                    $codeColumn = $columns[$pos];
                    break;
                }
            }
            if ($codeColumn === null) {
                foreach ($lower as $i => $c) {
                    if (strpos($c, 'code') !== false) {
                        $codeColumn = $columns[$i];
                        break;
                    }
                }
            }

            if ($codeColumn === null) {
                if (count($columns) >= 1) {
                    $codeColumn = $columns[0];
                } else {
                    return response()->json(['valid' => false, 'message' => 'No code column found']);
                }
            }

            // Check if the code exists in the database
            $exists = DB::table($table)
                ->where($codeColumn, $code)
                ->exists();

            return response()->json(['valid' => $exists]);

        } catch (QueryException $e) {
            Log::error('ICD10 validation query failed', ['exception' => $e->getMessage()]);
            return response()->json(['valid' => false, 'message' => 'Database error'], 500);
        } catch (\Throwable $e) {
            Log::error('ICD10 validation unexpected error', ['exception' => $e->getMessage()]);
            return response()->json(['valid' => false, 'message' => 'Server error'], 500);
        }
    }
}
