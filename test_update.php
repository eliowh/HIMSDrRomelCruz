<?php

require_once 'bootstrap/app.php';

use App\Models\StockOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Create a test order first
$user = User::where('role', 'pharmacy')->first();
if (!$user) {
    echo "No pharmacy user found. Creating one...\n";
    $user = User::create([
        'name' => 'Test Pharmacy',
        'email' => 'test@pharmacy.com',
        'password' => bcrypt('password'),
        'role' => 'pharmacy'
    ]);
}

// Authenticate as this user
Auth::login($user);

// Create a test order
$order = StockOrder::create([
    'user_id' => $user->id,
    'item_code' => 'TEST001',
    'generic_name' => 'Test Medicine',
    'brand_name' => 'Test Brand',
    'quantity' => 5,
    'unit_price' => 10.00,
    'total_price' => 50.00,
    'status' => 'pending',
    'requested_at' => now()
]);

echo "Created test order with ID: {$order->id}\n";
echo "Original quantity: {$order->quantity}\n";

// Now update the quantity
$order->quantity = 10;
$order->calculateTotalPrice();
$order->save();

echo "Updated quantity: {$order->quantity}\n";
echo "Updated total price: {$order->total_price}\n";

// Test the controller method directly
$controller = app()->make('App\Http\Controllers\PharmacyController');
$request = new \Illuminate\Http\Request([
    'quantity' => 15,
    'notes' => 'Updated from test'
]);

$response = $controller->updateOrder($request, $order->id);
$responseData = $response->getData(true);

echo "Controller response: " . json_encode($responseData) . "\n";

// Check if the order was actually updated
$order->refresh();
echo "Final quantity: {$order->quantity}\n";
echo "Final total price: {$order->total_price}\n";
echo "Final notes: {$order->notes}\n";

// Clean up
$order->delete();
echo "Test completed and cleaned up.\n";