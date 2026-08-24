<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('height', 20)->nullable();   // e.g. 6'2"
            $table->string('weight', 20)->nullable();   // e.g. 195 lbs
            $table->unsignedSmallInteger('years_experience')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['height', 'weight', 'years_experience']);
        });
    }
};
