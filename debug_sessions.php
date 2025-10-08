<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Database Connection Test ===\n";
    
    // Test basic database connection
    $connection = DB::connection()->getPdo();
    echo "✓ Database connection successful!\n";
    
    // Check current database
    $currentDb = DB::select('SELECT DATABASE() as db')[0]->db;
    echo "✓ Current database: $currentDb\n";
    
    // List all tables
    echo "\n=== Available Tables ===\n";
    $tables = DB::select('SHOW TABLES');
    $tableKey = "Tables_in_$currentDb";
    
    foreach ($tables as $table) {
        echo "- " . $table->$tableKey . "\n";
    }
    
    // Check if sessions table exists specifically
    echo "\n=== Sessions Table Check ===\n";
    $sessionsExists = DB::select("SHOW TABLES LIKE 'sessions'");
    if (empty($sessionsExists)) {
        echo "❌ Sessions table does NOT exist\n";
        
        // Try to create it again
        echo "\nCreating sessions table...\n";
        DB::statement('CREATE TABLE sessions (
            id VARCHAR(255) PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload LONGTEXT NOT NULL,
            last_activity INT NOT NULL,
            INDEX sessions_user_id_index (user_id),
            INDEX sessions_last_activity_index (last_activity)
        )');
        echo "✓ Sessions table created!\n";
    } else {
        echo "✓ Sessions table exists\n";
        
        // Show table structure
        echo "\n=== Sessions Table Structure ===\n";
        $structure = DB::select('DESCRIBE sessions');
        foreach ($structure as $column) {
            echo "- {$column->Field}: {$column->Type}\n";
        }
    }
    
    // Test a simple session operation
    echo "\n=== Session Test ===\n";
    try {
        $testSessionId = 'test_' . time();
        DB::table('sessions')->insert([
            'id' => $testSessionId,
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => 'test_payload',
            'last_activity' => time()
        ]);
        echo "✓ Successfully inserted test session\n";
        
        // Clean up test data
        DB::table('sessions')->where('id', $testSessionId)->delete();
        echo "✓ Test session cleaned up\n";
        
    } catch (Exception $e) {
        echo "❌ Session test failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}