<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Run the application
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Comparing patient views implementation...\n\n";

echo "1. Checking PatientController::index method (nurse view):\n";
try {
    $q = null;
    $patients = App\Models\Patient::when($q, function ($query, $q) {
            $query->where(function ($s) use ($q) {
                $s->where('first_name','like',"%{$q}%")
                  ->orWhere('last_name','like',"%{$q}%")
                  ->orWhere('middle_name','like',"%{$q}%")
                  ->orWhere('patient_no','like',"%{$q}%");
            });
        })
        ->orderByDesc('patient_no')
        ->paginate(20)
        ->withQueryString();

    echo "   - Patients count: " . $patients->count() . "\n";
    if ($patients->count() > 0) {
        echo "   - First patient: " . $patients->first()->first_name . " " . $patients->first()->last_name . "\n";
    }
} catch (\Exception $e) {
    echo "   - Error: " . $e->getMessage() . "\n";
}

echo "\n2. Checking PatientController::labtechPatients method:\n";
try {
    $q = null;
    $patients = App\Models\Patient::when($q, function ($query, $q) {
            $query->where(function ($s) use ($q) {
                $s->where('first_name','like',"%{$q}%")
                  ->orWhere('last_name','like',"%{$q}%")
                  ->orWhere('middle_name','like',"%{$q}%")
                  ->orWhere('patient_no','like',"%{$q}%");
            });
        })
        ->orderByDesc('patient_no')
        ->paginate(20)
        ->withQueryString();

    echo "   - Patients count: " . $patients->count() . "\n";
    if ($patients->count() > 0) {
        echo "   - First patient: " . $patients->first()->first_name . " " . $patients->first()->last_name . "\n";
    }
} catch (\Exception $e) {
    echo "   - Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing view rendering for labtech_patients:\n";
try {
    $q = '';
    $patients = App\Models\Patient::orderByDesc('patient_no')
        ->paginate(20)
        ->withQueryString();

    // Test if view exists
    echo "   - Does view exist? " . (view()->exists('labtech.labtech_patients') ? 'Yes' : 'No') . "\n";
    
    echo "   - Patients in variable: " . $patients->count() . "\n";
    
    // Try to render partial view
    $html = view('labtech.labtech_patients', compact('patients', 'q'))->render();
    echo "   - View rendered successfully!\n";
} catch (\Exception $e) {
    echo "   - Error rendering view: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";