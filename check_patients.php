<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Run the application
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Checking database connection...\n";
try {
    $connection = DB::connection()->getPdo();
    echo "Database connected successfully. Database name: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

echo "Counting patients: " . App\Models\Patient::count() . "\n";

// Simulate the controller method
echo "Simulating labtechPatients controller method...\n";
$patients = App\Models\Patient::orderByDesc('patient_no')
    ->paginate(20);

echo "Patients found: " . $patients->count() . "\n";

if ($patients->count() > 0) {
    echo "First patient: " . $patients->first()->first_name . " " . $patients->first()->last_name . "\n";
} else {
    echo "No patients found!\n";
}

echo "Done.\n";