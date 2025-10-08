<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Fixing MariaDB Tablespace Issue ===\n";
    
    // Step 1: Discard the corrupted tablespace
    echo "Step 1: Discarding corrupted tablespace...\n";
    try {
        DB::statement('ALTER TABLE sessions DISCARD TABLESPACE');
        echo "✓ Tablespace discarded\n";
    } catch (Exception $e) {
        echo "Note: Could not discard tablespace: " . $e->getMessage() . "\n";
        echo "Trying to drop table completely...\n";
        
        try {
            DB::statement('DROP TABLE sessions');
            echo "✓ Table dropped\n";
        } catch (Exception $e2) {
            echo "Trying force drop...\n";
            // Force drop by removing from information schema if needed
            try {
                DB::statement('SET foreign_key_checks = 0');
                DB::statement('DROP TABLE IF EXISTS sessions');
                DB::statement('SET foreign_key_checks = 1');
                echo "✓ Force drop successful\n";
            } catch (Exception $e3) {
                echo "Error in force drop: " . $e3->getMessage() . "\n";
            }
        }
    }
    
    // Step 2: Create the table with a different name first, then rename
    echo "\nStep 2: Creating new sessions table...\n";
    
    // Create with temporary name
    DB::statement('CREATE TABLE sessions_new (
        id VARCHAR(255) NOT NULL PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload LONGTEXT NOT NULL,
        last_activity INT NOT NULL,
        KEY sessions_user_id_index (user_id),
        KEY sessions_last_activity_index (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    
    echo "✓ Created sessions_new table\n";
    
    // Drop old table if it still exists and rename new one
    try {
        DB::statement('DROP TABLE IF EXISTS sessions');
        echo "✓ Removed old sessions table\n";
    } catch (Exception $e) {
        echo "Note: " . $e->getMessage() . "\n";
    }
    
    DB::statement('RENAME TABLE sessions_new TO sessions');
    echo "✓ Renamed sessions_new to sessions\n";
    
    // Step 3: Verify the table works
    echo "\nStep 3: Testing the new table...\n";
    
    $structure = DB::select('DESCRIBE sessions');
    echo "✓ Table structure verified:\n";
    foreach ($structure as $column) {
        echo "  - {$column->Field}: {$column->Type}\n";
    }
    
    // Test insertion
    $testId = 'test_fix_' . time();
    DB::table('sessions')->insert([
        'id' => $testId,
        'user_id' => null,
        'ip_address' => '127.0.0.1', 
        'user_agent' => 'Test Fix Agent',
        'payload' => base64_encode('test_session_payload'),
        'last_activity' => time()
    ]);
    echo "✓ Test session inserted\n";
    
    // Test retrieval
    $session = DB::table('sessions')->where('id', $testId)->first();
    if ($session) {
        echo "✓ Test session retrieved: {$session->id}\n";
    }
    
    // Clean up test
    DB::table('sessions')->where('id', $testId)->delete();
    echo "✓ Test session cleaned up\n";
    
    echo "\n=== SUCCESS! ===\n";
    echo "Sessions table has been fixed and is working properly!\n";
    echo "You can now use the application without session errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}