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
        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'final_diagnosis')) {
                $table->text('final_diagnosis')->nullable()->after('admission_diagnosis');
            }
            if (!Schema::hasColumn('admissions', 'final_diagnosis_description')) {
                $table->text('final_diagnosis_description')->nullable()->after('final_diagnosis');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'final_diagnosis_description')) {
                $table->dropColumn('final_diagnosis_description');
            }
            if (Schema::hasColumn('admissions', 'final_diagnosis')) {
                $table->dropColumn('final_diagnosis');
            }
        });
    }
};
