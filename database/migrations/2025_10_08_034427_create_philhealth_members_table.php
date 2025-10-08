<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('philhealth_members', function (Blueprint $table) {
            $table->id();
            $table->string('philhealth_number')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('member_type')->default('Active'); // Active, Lifetime, Indigent, etc.
            $table->string('category')->default('Direct Contributor'); // Direct, Indirect, Sponsored
            $table->decimal('premium_amount', 8, 2)->default(0);
            $table->date('effectivity_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('employer')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('philhealth_members');
    }
};
