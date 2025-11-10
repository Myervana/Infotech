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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('room_title');
            $table->string('device_category');
            $table->string('device_type')->nullable(); // Auto-assigned based on category
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number');
            $table->text('description')->nullable();
            $table->enum('status', ['Usable', 'Unusable'])->default('Usable');
            $table->string('barcode')->unique(); // Auto-generated barcode
            $table->string('photo')->nullable(); // Path to uploaded photo
            $table->timestamps();

            // Indexes for better performance
            $table->index(['room_title', 'device_category']);
            $table->index(['room_title', 'device_category', 'serial_number']);
            $table->index('barcode');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};