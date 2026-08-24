<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('image');       // image | video
            $table->string('file_path')->nullable();        // resized original
            $table->string('thumb_path')->nullable();       // square crop
            $table->string('video_url', 500)->nullable();   // embed link, never an upload
            $table->string('caption')->nullable();
            $table->date('taken_on')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
