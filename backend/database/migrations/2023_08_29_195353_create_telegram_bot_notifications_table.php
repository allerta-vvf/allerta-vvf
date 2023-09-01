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
        Schema::create('telegram_bot_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->boolean('type_team_state')->default(true);
            $table->varchar('last_message_hash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_notifications');
    }
};
