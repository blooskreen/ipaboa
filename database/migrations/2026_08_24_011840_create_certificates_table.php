<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('certifiable');   // CourseCompletion or QuizAttempt
            $table->string('title');
            $table->string('serial')->unique();
            $table->timestamp('issued_at');
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            // One certificate per source record, ever.
            $table->unique(['certifiable_type', 'certifiable_id'], 'certificate_unique_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
