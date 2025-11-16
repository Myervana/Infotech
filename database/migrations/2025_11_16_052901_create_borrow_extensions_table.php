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
        Schema::create('borrow_extensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrow_id');
            $table->string('extension_duration'); // '1_day', '2_days', '3_days', '4_days', '1_week'
            $table->integer('days_added'); // Number of days added
            $table->text('reason'); // Reason for extension
            $table->datetime('previous_due_date')->nullable(); // Previous due date before extension
            $table->datetime('new_due_date'); // New due date after extension
            $table->datetime('extended_at'); // When the extension was made
            $table->timestamps();

            $table->foreign('borrow_id')->references('id')->on('borrows')->onDelete('cascade');
            $table->index('borrow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_extensions');
    }
};
