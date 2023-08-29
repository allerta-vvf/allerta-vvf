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
        Schema::create('telegram_bot_logins', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique()->nullable();
            $table->string('tmp_login_code')->unique()->nullable();
            $table->unsignedBigInteger('user')->unsigned();
            $table->foreign('user')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_logins');
    }
};
