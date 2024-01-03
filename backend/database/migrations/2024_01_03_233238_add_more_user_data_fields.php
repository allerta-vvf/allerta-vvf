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
            $table->string('surname')->nullable()->after('name');
            $table->string('birthplace')->nullable()->after('surname');
            $table->string('birthplace_province')->nullable()->after('birthplace');
            $table->string('ssn')->nullable()->after('birthplace_province');
            $table->string('address')->nullable()->after('ssn');
            $table->timestamp('birthday')->nullable()->after('last_availability_change');
            $table->timestamp('course_date')->nullable()->after('birthday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('surname');
            $table->dropColumn('birthplace');
            $table->dropColumn('birthplace_province');
            $table->dropColumn('ssn');
            $table->dropColumn('address');
            $table->dropColumn('birthday');
            $table->dropColumn('course_date');
        });
    }
};
