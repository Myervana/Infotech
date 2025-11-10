<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('share_token_id')->nullable()->constrained('share_tokens')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['owner_user_id', 'shared_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_access');
    }
};


