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
        Schema::create('ip_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->string('email')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('action_type'); // 'visit', 'login_attempt', 'login_success'
            $table->boolean('success')->default(false);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('timezone')->nullable();
            $table->string('accuracy')->nullable(); // Location accuracy level
            $table->timestamp('last_seen');
            $table->timestamps();
            
            $table->index(['ip_address', 'action_type']);
            $table->index('last_seen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_tracking');
    }
};
