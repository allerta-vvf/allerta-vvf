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
        Schema::create('place_provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('short_name', 2);
            $table->string('region', 25);
        });
        Schema::create('place_municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('name', 200);
            $table->string('foreign_name', 200)->nullable();
            $table->string('cadastral_code', 4)->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->string('prefix', 4)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('pec', 200)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('fax', 30)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('province_id')->constrained('place_provinces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_municipalities');
        Schema::dropIfExists('place_provinces');
    }
};
