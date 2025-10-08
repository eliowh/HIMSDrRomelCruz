<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Test basic database connection
    echo "Testing database connection...\n";
    $connection = DB::connection()->getPdo();
    echo "Database connection successful!\n";
    
    // Check if database exists
    $databaseName = env('DB_DATABASE');
    echo "Checking database: $databaseName\n";
    
    // Create migrations table using raw SQL
    echo "Creating migrations table with raw SQL...\n";
    DB::statement('CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL
    )');
    echo "Migrations table created!\n";
    
    // Create sessions table using raw SQL
    echo "Creating sessions table with raw SQL...\n";
    DB::statement('CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        payload LONGTEXT NOT NULL,
        last_activity INT NOT NULL,
        INDEX sessions_user_id_index (user_id),
        INDEX sessions_last_activity_index (last_activity)
    )');
    echo "Sessions table created!\n";
    
    echo "All tables created successfully using raw SQL!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}