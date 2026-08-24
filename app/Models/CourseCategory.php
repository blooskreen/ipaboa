<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CourseCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
