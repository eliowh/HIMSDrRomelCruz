<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdmissionFieldsToPatientsTable extends Migration
{
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('room_no')->nullable()->after('patient_no');
            $table->string('admission_type')->nullable()->after('room_no');
            $table->string('service')->nullable()->after('admission_type');
            $table->string('doctor_name')->nullable()->after('service');
            $table->string('doctor_type')->nullable()->after('doctor_name');
            $table->text('admission_diagnosis')->nullable()->after('doctor_type');
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'room_no',
                'admission_type',
                'service',
                'doctor_name',
                'doctor_type',
                'admission_diagnosis',
            ]);
        });
    }
}
