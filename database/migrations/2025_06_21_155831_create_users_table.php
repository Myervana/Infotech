<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_accounts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('photo')->nullable(); // to store uploaded photo filename
        $table->timestamps();
    });
}
};

