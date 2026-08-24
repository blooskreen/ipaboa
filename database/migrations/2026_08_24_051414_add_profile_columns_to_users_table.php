<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('photo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('classification')->nullable();
            $table->boolean('profile_public')->default(false);
            $table->boolean('is_first_year')->default(false);
            $table->date('first_year_ends_at')->nullable();
            $table->boolean('email_opt_out')->default(false);

            $table->index('profile_public');
            $table->index('is_first_year');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'photo_path', 'banner_path', 'bio', 'phone', 'city',
                'classification', 'profile_public', 'is_first_year',
                'first_year_ends_at', 'email_opt_out',
            ]);
        });
    }
};
