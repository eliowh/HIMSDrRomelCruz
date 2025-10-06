<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Inspect table structure and sample rows
$cols = \DB::select('SHOW COLUMNS FROM doctorslist');
$sample = \DB::table('doctorslist')->limit(10)->get();
echo "COLUMNS:\n" . json_encode($cols, JSON_PRETTY_PRINT) . "\n\n";
echo "SAMPLE ROWS:\n" . json_encode($sample, JSON_PRETTY_PRINT) . "\n";
