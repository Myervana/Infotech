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
        Schema::create('maintenance_notes', function (Blueprint $table) {
            $table->id();
            $table->string('fullset_id')->unique(); // e.g., 'CL1-PC001'
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index('fullset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_notes');
    }
};