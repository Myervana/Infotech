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
        Schema::table('maintenance_notes', function (Blueprint $table) {
            // Remove unique constraint on fullset_id first
            $table->dropUnique(['fullset_id']);
        });
        
        // Use DB facade to modify column since change() requires doctrine/dbal
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE maintenance_notes MODIFY fullset_id VARCHAR(255) NULL');
        
        Schema::table('maintenance_notes', function (Blueprint $table) {
            // Add item_id for item-level notes
            $table->unsignedBigInteger('room_item_id')->nullable()->after('fullset_id');
            $table->foreign('room_item_id')->references('id')->on('room_items')->onDelete('cascade');
            
            // Add reason field for item-level notes
            $table->text('reason')->nullable()->after('note');
            
            // Add index for room_item_id
            $table->index('room_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_notes', function (Blueprint $table) {
            $table->dropForeign(['room_item_id']);
            $table->dropIndex(['room_item_id']);
            $table->dropColumn(['room_item_id', 'reason']);
        });
        
        // Use DB facade to modify column back
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE maintenance_notes MODIFY fullset_id VARCHAR(255) NOT NULL');
        
        Schema::table('maintenance_notes', function (Blueprint $table) {
            $table->unique('fullset_id');
        });
    }
};
