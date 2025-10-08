<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== MySQL Engine Diagnosis ===\n";
    
    // Check MySQL version
    $version = DB::select('SELECT VERSION() as version')[0]->version;
    echo "MySQL Version: $version\n";
    
    // Check table status
    echo "\n=== Table Status ===\n";
    try {
        $tableStatus = DB::select("SHOW TABLE STATUS LIKE 'sessions'");
        if (!empty($tableStatus)) {
            $status = $tableStatus[0];
            echo "Engine: " . ($status->Engine ?? 'Unknown') . "\n";
            echo "Version: " . ($status->Version ?? 'Unknown') . "\n";
            echo "Rows: " . ($status->Rows ?? 'Unknown') . "\n";
            echo "Data Length: " . ($status->Data_length ?? 'Unknown') . "\n";
            echo "Comment: " . ($status->Comment ?? 'None') . "\n";
        }
    } catch (Exception $e) {
        echo "Error getting table status: " . $e->getMessage() . "\n";
    }
    
    // Drop and recreate the sessions table with explicit engine
    echo "\n=== Recreating Sessions Table ===\n";
    
    try {
        DB::statement('DROP TABLE IF EXISTS sessions');
        echo "✓ Dropped existing sessions table\n";
    } catch (Exception $e) {
        echo "Note: Could not drop table: " . $e->getMessage() . "\n";
    }
    
    // Create with explicit InnoDB engine
    DB::statement('CREATE TABLE sessions (
        id VARCHAR(255) NOT NULL PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload LONGTEXT NOT NULL,
        last_activity INT NOT NULL,
        KEY sessions_user_id_index (user_id),
        KEY sessions_last_activity_index (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    
    echo "✓ Created sessions table with InnoDB engine\n";
    
    // Verify the new table
    echo "\n=== Verification ===\n";
    $structure = DB::select('DESCRIBE sessions');
    echo "✓ Table structure verified:\n";
    foreach ($structure as $column) {
        echo "  - {$column->Field}: {$column->Type}\n";
    }
    
    // Test session insertion
    echo "\n=== Functionality Test ===\n";
    $testId = 'test_' . time();
    DB::table('sessions')->insert([
        'id' => $testId,
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test User Agent',
        'payload' => base64_encode('test_session_data'),
        'last_activity' => time()
    ]);
    echo "✓ Test session inserted successfully\n";
    
    // Read it back
    $session = DB::table('sessions')->where('id', $testId)->first();
    if ($session) {
        echo "✓ Test session retrieved successfully\n";
        echo "  Session ID: {$session->id}\n";
        echo "  Last Activity: {$session->last_activity}\n";
    }
    
    // Clean up
    DB::table('sessions')->where('id', $testId)->delete();
    echo "✓ Test session cleaned up\n";
    
    echo "\n=== SUCCESS ===\n";
    echo "Sessions table is now working properly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}