<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_course_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_category_id')->constrained()->cascadeOnDelete();

            $table->unique(['course_id', 'course_category_id'], 'course_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_course_category');
    }
};
