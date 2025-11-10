<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_items', function (Blueprint $table) {
            $table->string('barcode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Prevent rollback failure: update all NULL barcode values before making it NOT NULL
        DB::table('room_items')
            ->whereNull('barcode')
            ->update(['barcode' => 'UNKNOWN']);

        Schema::table('room_items', function (Blueprint $table) {
            $table->string('barcode')->nullable(false)->change();
        });
    }
};
