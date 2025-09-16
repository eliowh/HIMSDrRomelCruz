<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Icd10SampleSeeder extends Seeder
{
    public function run()
    {
        $table = 'icd10namepricerate';

        // If the table doesn't exist, create a minimal schema to host sample rows.
        if (!Schema::hasTable($table)) {
            Schema::create($table, function ($tableObj) {
                $tableObj->increments('id');
                $tableObj->string('code', 32)->nullable();
                $tableObj->string('description', 512)->nullable();
            });
        }

        // Insert sample rows (use insertOrIgnore to be idempotent)
        $cols = Schema::getColumnListing($table);
        // Choose the first column as code and second as description if real names aren't obvious
        $codeCol = $cols[0] ?? null;
        $descCol = $cols[1] ?? null;

        if (!$codeCol || !$descCol) {
            // nothing we can do
            return;
        }

        $rows = [
            [$codeCol => 'A00', $descCol => 'Cholera'],
            [$codeCol => 'A01', $descCol => 'Typhoid and paratyphoid fevers'],
            [$codeCol => 'A02', $descCol => 'Other salmonella infections'],
            [$codeCol => 'B01', $descCol => 'Varicella [chickenpox]'],
            [$codeCol => 'J18', $descCol => 'Pneumonia, unspecified organism'],
            [$codeCol => 'I10', $descCol => 'Essential (primary) hypertension'],
            [$codeCol => 'E11', $descCol => 'Type 2 diabetes mellitus']
        ];

        DB::table($table)->insertOrIgnore($rows);
    }
}
