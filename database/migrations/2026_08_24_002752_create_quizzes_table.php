<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedTinyInteger('passing_percentage')->default(70);
            $table->unsignedSmallInteger('max_attempts')->nullable();      // null = unlimited
            $table->unsignedSmallInteger('time_limit_minutes')->nullable(); // null = untimed
            $table->string('reveal_answers')->default('wrong');             // none | wrong | full
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('produces_certificate')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
