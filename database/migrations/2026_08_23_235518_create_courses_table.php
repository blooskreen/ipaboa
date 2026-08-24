<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();      // rich text HTML
            $table->string('content_type')->default('link'); // link | video | document | text
            $table->string('content_url')->nullable();     // embed URL, never an upload
            $table->text('body')->nullable();              // inline rich text content
            $table->decimal('hours', 5, 2)->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_published')->default(false);
            $table->boolean('produces_certificate')->default(false);
            $table->boolean('is_first_year')->default(false);
            $table->string('image_path')->nullable();
            $table->jsonb('instructors')->nullable();      // array of names
            $table->string('token', 40)->unique();         // QR check-in
            $table->timestamps();

            $table->index('is_published');
            $table->index('is_first_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
