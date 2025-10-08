<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up()
    {
        // Create a billing user for testing
        $existingUser = DB::table('users')->where('email', 'billing@test.com')->first();
        
        if (!$existingUser) {
            DB::table('users')->insert([
                'name' => 'Billing User',
                'email' => 'billing@test.com',
                'password' => Hash::make('password123'),
                'role' => 'billing',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('users')->where('email', 'billing@test.com')->delete();
    }
};