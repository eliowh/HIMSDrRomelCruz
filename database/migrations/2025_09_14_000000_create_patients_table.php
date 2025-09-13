<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_no')->unique()->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->unsignedSmallInteger('age_years')->nullable();
            $table->unsignedSmallInteger('age_months')->nullable();
            $table->unsignedSmallInteger('age_days')->nullable();
            $table->string('province')->default('Bulacan');
            $table->string('city')->default('Malolos City');
            $table->string('barangay')->nullable();
            $table->string('nationality')->default('Filipino');
            $table->timestamps();

            $table->index('patient_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
}
?>