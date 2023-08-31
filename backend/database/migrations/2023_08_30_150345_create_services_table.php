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
        Schema::create('services_types', function (Blueprint $table) {
            $table->bigIncrements('id');        
            $table->string('name')->unique();
        });

        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->double('lat');
            $table->double('lon');
            $table->string('place_id')->nullable();
            $table->string('osm_id')->nullable();
            $table->string('osm_type')->nullable();
            $table->string('licence')->nullable();
            $table->string('addresstype')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('road')->nullable();
            $table->string('house_number')->nullable();
            $table->string('postcode')->nullable();
            $table->string('state')->nullable();
            $table->string('village')->nullable();
            $table->string('suburb')->nullable();
            $table->string('city')->nullable();
            $table->string('municipality')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('chief_id')->constrained('users');
            $table->foreignId('place_id')->constrained('places');
            $table->foreignId('type_id')->constrained('services_types');
            $table->text('notes')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->foreignId('added_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->foreignId('deleted_by_id')->nullable()->constrained('users');
            $table->softDeletes();
            $table->unique(['code']);            
            $table->timestamps();
        });

        Schema::create('services_drivers', function (Blueprint $table) {
            $table->bigIncrements('id');        
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->unsigned();
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services')->onDelete('cascade');
        });

        Schema::create('services_crew', function (Blueprint $table) {
            $table->bigIncrements('id');        
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->unsigned();
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services_drivers');
        Schema::dropIfExists('services_crew');
        Schema::dropIfExists('services');
        Schema::dropIfExists('services_types');
        Schema::dropIfExists('places');
    }
};
