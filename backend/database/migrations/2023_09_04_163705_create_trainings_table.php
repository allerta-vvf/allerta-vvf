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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->foreignId('chief_id')->constrained('users');
            $table->string('place');
            $table->string('notes')->nullable();
            $table->foreignId('added_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->unique(['name']);
            $table->timestamps();
        });

        Schema::create('trainings_crew', function (Blueprint $table) {
            $table->bigIncrements('id');        
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('training_id')->unsigned();
            $table->foreign('training_id')
                  ->references('id')
                  ->on('trainings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
