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
        Schema::table('borrows', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('department');
            $table->string('borrow_duration')->nullable()->after('reason'); // '1_day', '2_days', '3_days', '4_days', '1_week'
            $table->datetime('due_date')->nullable()->after('borrow_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['reason', 'borrow_duration', 'due_date']);
        });
    }
};
