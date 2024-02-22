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
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn('municipality');
            $table->foreignId('municipality_id')->constrained('place_municipalities')->nullable();
            $table->float('lat', 10, 6)->nullable()->change();
            $table->float('lon', 10, 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn('municipality_id');
            $table->string('municipality')->nullable();
            $table->float('lat', 10, 6)->nullable(false)->change();
            $table->float('lon', 10, 6)->nullable(false)->change();
        });
    }
};
