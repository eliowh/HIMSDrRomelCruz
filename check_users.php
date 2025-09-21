<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Run the application
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Checking user roles...\n";
$users = App\Models\User::select('id', 'name', 'email', 'role')->get();

echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
}

echo "\nUsers by role:\n";
$roles = $users->groupBy('role');
foreach ($roles as $role => $roleUsers) {
    echo "{$role}: {$roleUsers->count()} users\n";
}

echo "\nDone.\n";