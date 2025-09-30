<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'age_years')) {
                $table->dropColumn('age_years');
            }
            if (Schema::hasColumn('patients', 'age_months')) {
                $table->dropColumn('age_months');
            }
            if (Schema::hasColumn('patients', 'age_days')) {
                $table->dropColumn('age_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'age_years')) {
                $table->unsignedSmallInteger('age_years')->nullable();
            }
            if (!Schema::hasColumn('patients', 'age_months')) {
                $table->unsignedSmallInteger('age_months')->nullable();
            }
            if (!Schema::hasColumn('patients', 'age_days')) {
                $table->unsignedSmallInteger('age_days')->nullable();
            }
        });
    }
};
