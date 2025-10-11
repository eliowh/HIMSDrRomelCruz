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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('attachment_filename')->nullable()->after('message');
            $table->string('attachment_original_name')->nullable()->after('attachment_filename');
            $table->string('attachment_mime_type')->nullable()->after('attachment_original_name');
            $table->integer('attachment_size')->nullable()->after('attachment_mime_type');
            $table->string('attachment_path')->nullable()->after('attachment_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_filename',
                'attachment_original_name', 
                'attachment_mime_type',
                'attachment_size',
                'attachment_path'
            ]);
        });
    }
};
