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
        Schema::create('telegram_special_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('message_id');
            $table->integer('user_id')->nullable();
            $table->integer('chat_id');
            $table->string('chat_type');
            $table->string('type');
            $table->integer('resource_id')->nullable();
            $table->string('resource_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_special_messages');
    }
};
