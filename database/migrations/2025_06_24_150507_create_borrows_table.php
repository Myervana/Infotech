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
    Schema::create('borrows', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('room_item_id');
        $table->string('borrower_name');
        $table->date('borrow_date');
        $table->date('return_date')->nullable();
        $table->string('status')->default('Borrowed'); // Borrowed, Returned
        $table->timestamps();

        $table->foreign('room_item_id')->references('id')->on('room_items')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrows');
    }
};
