<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check if user already exists
$existingUser = User::where('email', 'test.doctor@hospital.com')->first();

if ($existingUser) {
    echo "Doctor account already exists!\n";
    echo "Email: test.doctor@hospital.com\n";
    echo "Password: password123\n";
} else {
    // Create new doctor user
    $user = User::create([
        'name' => 'Dr. Test Doctor',
        'email' => 'test.doctor@hospital.com',
        'password' => Hash::make('password123'),
        'role' => 'doctor',
    ]);

    echo "Doctor account created successfully!\n";
    echo "Email: test.doctor@hospital.com\n";
    echo "Password: password123\n";
    echo "User ID: " . $user->id . "\n";
}