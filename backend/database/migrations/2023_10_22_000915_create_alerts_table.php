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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->boolean('closed')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('added_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('alert_crews', function (Blueprint $table) {
            $table->id();
            $table->boolean('accepted')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('responded_at')->nullable();
        });

        Schema::create('alerts_crew_associations', function (Blueprint $table) {
            $table->bigIncrements('id');    
            $table->unsignedBigInteger('alert_crew_id')->unsigned();
            $table->foreign('alert_crew_id')
                  ->references('id')
                  ->on('alert_crews')->onDelete('cascade');
            $table->unsignedBigInteger('alert_id')->unsigned();
            $table->foreign('alert_id')
                  ->references('id')
                  ->on('alerts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts_crew_associations');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('alert_crews');
    }
};
