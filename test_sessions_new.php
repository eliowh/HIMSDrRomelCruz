<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Testing Sessions_New Table ===\n";
    
    // Let's test if sessions_new works and just use that
    $structure = DB::select('DESCRIBE sessions_new');
    echo "✓ sessions_new table structure:\n";
    foreach ($structure as $column) {
        echo "  - {$column->Field}: {$column->Type}\n";
    }
    
    // Test functionality
    $testId = 'working_test_' . time();
    DB::table('sessions_new')->insert([
        'id' => $testId,
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Working Test Agent',
        'payload' => base64_encode('working_session_data'),
        'last_activity' => time()
    ]);
    echo "✓ Test session inserted successfully\n";
    
    $session = DB::table('sessions_new')->where('id', $testId)->first();
    if ($session) {
        echo "✓ Test session retrieved successfully\n";
        echo "  ID: {$session->id}\n";
        echo "  Last Activity: {$session->last_activity}\n";
    }
    
    // Clean up
    DB::table('sessions_new')->where('id', $testId)->delete();
    echo "✓ Test session cleaned up\n";
    
    echo "\n=== SOLUTION ===\n";
    echo "The sessions_new table works perfectly!\n";
    echo "We can either:\n";
    echo "1. Use sessions_new as the session table (modify config)\n";
    echo "2. Switch to file-based sessions temporarily\n";
    echo "3. Try to fix the tablespace issue in MySQL directly\n";
    
    echo "\nRecommendation: Let's switch to file sessions for now.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}