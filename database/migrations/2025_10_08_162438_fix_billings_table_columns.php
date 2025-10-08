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
        Schema::table('billings', function (Blueprint $table) {
            // Rename professional_fee to professional_fees
            $table->renameColumn('professional_fee', 'professional_fees');
            
            // Add missing columns that the model expects
            $table->decimal('philhealth_deduction', 10, 2)->default(0)->after('total_amount');
            $table->decimal('net_amount', 10, 2)->default(0)->after('philhealth_deduction');
            $table->boolean('is_senior_citizen')->default(false)->after('is_philhealth_member');
            $table->boolean('is_pwd')->default(false)->after('is_senior_citizen');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_pwd');
            
            // Add billing_date column if it doesn't exist
            if (!Schema::hasColumn('billings', 'billing_date')) {
                $table->datetime('billing_date')->nullable()->after('created_by');
            }
            
            // Modify status enum to match what the code expects
            $table->enum('status', ['pending', 'paid', 'cancelled', 'active', 'discharged'])->default('pending')->change();
            
            // Drop columns that are not needed or renamed
            $table->dropColumn(['is_senior_pwd', 'philhealth_coverage', 'philhealth_number', 'subtotal', 'admission_date', 'discharge_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('professional_fees', 'professional_fee');
            $table->dropColumn(['philhealth_deduction', 'net_amount', 'is_senior_citizen', 'is_pwd', 'created_by', 'billing_date']);
            
            // Restore original columns
            $table->boolean('is_senior_pwd')->default(false);
            $table->decimal('philhealth_coverage', 10, 2)->default(0);
            $table->string('philhealth_number')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->date('admission_date');
            $table->date('discharge_date')->nullable();
            
            // Restore original status enum
            $table->enum('status', ['active', 'discharged', 'paid', 'cancelled'])->default('active')->change();
        });
    }
};
