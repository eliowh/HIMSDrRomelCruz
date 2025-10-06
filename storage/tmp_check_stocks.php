<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

// Boot Eloquent outside Laravel
$paths = [__DIR__ . '/../vendor/autoload.php'];

// Basic setup using existing project .env is complicated; use raw PDO to query
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'drromelcruzhp';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
$user = $dbUser;
$pass = $dbPass;

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to DB\n";

    // Describe table
    $stmt = $pdo->query("SHOW COLUMNS FROM `stock_price`");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "COLUMNS: \n" . json_encode($cols, JSON_PRETTY_PRINT) . "\n\n";

    // Show sample rows
    $stmt = $pdo->query("SELECT * FROM `stock_price` LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "SAMPLE ROWS:\n" . json_encode($rows, JSON_PRETTY_PRINT) . "\n";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
