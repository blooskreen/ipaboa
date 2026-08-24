<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('enrolled');  // enrolled | pending | approved
            $table->decimal('hours_credited', 5, 2)->default(0);
            $table->string('season')->nullable();           // season label at time of credit
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // A member may repeat a course in a later season, but not twice in one.
            $table->unique(['user_id', 'course_id', 'season'], 'completion_unique_per_season');
            $table->index(['user_id', 'season']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_completions');
    }
};
