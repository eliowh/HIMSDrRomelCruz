<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$table = 'icd10namepricerate';
try {
    if (!Schema::hasTable($table)) {
        echo "TABLE_NOT_FOUND\n";
        exit(0);
    }
    $cols = Schema::getColumnListing($table);
    echo json_encode($cols, JSON_PRETTY_PRINT) . "\n";

    // Also run SHOW COLUMNS for full details
    $rows = DB::select('SHOW COLUMNS FROM `' . $table . '`');
    echo json_encode($rows, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
