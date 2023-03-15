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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('phone_number')->nullable(); //https://github.com/googlei18n/libphonenumber/blob/master/FALSEHOODS.md
            $table->boolean('available')->default(false);
            $table->boolean('availability_manual_mode')->default(false);
            $table->integer('availability_minutes')->default(0);
            $table->boolean('chief')->default(false);
            $table->boolean('driver')->default(false);
            $table->integer('services')->default(0);
            $table->integer('trainings')->default(0);
            $table->timestamp('last_access')->nullable();
            $table->boolean('banned')->default(false);
            $table->boolean('hidden')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'phone_number', 'available', 'chief', 'driver', 'services', 'trainings', 'last_access', 'banned', 'hidden']);
        });
    }
};
