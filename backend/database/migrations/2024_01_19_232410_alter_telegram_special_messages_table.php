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
        Schema::table('telegram_special_messages', function (Blueprint $table) {
            $table->bigInteger('message_id')->change();
            $table->bigInteger('user_id')->change();
            $table->bigInteger('chat_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('telegram_special_messages', function (Blueprint $table) {
            $table->integer('message_id')->change();
            $table->integer('user_id')->change();
            $table->integer('chat_id')->change();
        });
    }
};
