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
            $table->string('borrower_photo')->nullable()->after('borrower_name');
            $table->string('position')->nullable()->after('borrower_photo');
            $table->enum('department', ['BSIT', 'BSHM', 'BSBA', 'BSED', 'BEED'])->nullable()->after('position');
            $table->decimal('latitude', 10, 8)->nullable()->after('department');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['borrower_photo', 'position', 'department', 'latitude', 'longitude']);
        });
    }
};
