<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('mc');       // mc | tf | short
            $table->text('prompt');
            $table->jsonb('options')->nullable();        // ["Option A", "Option B", ...]
            $table->jsonb('correct_answer')->nullable(); // ["Option B"] -- values, not indexes
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
