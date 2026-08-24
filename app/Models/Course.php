<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    public const TYPE_LINK     = 'link';
    public const TYPE_VIDEO    = 'video';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_TEXT     = 'text';

    public const TYPES = [
        self::TYPE_LINK     => 'External link',
        self::TYPE_VIDEO    => 'Video embed',
        self::TYPE_DOCUMENT => 'Document link',
        self::TYPE_TEXT     => 'Written content',
    ];

    protected $fillable = [
        'title', 'description', 'content_type', 'content_url', 'body', 'hours',
        'requires_approval', 'is_published', 'produces_certificate', 'is_first_year',
        'image_path', 'instructors', 'token',
    ];

    protected $casts = [
        'hours'                => 'decimal:2',
        'requires_approval'    => 'boolean',
        'is_published'         => 'boolean',
        'produces_certificate' => 'boolean',
        'is_first_year'        => 'boolean',
        'instructors'          => 'array',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CourseCategory::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(CourseCompletion::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function embedUrl(): ?string
    {
        return \App\Support\Embed::url($this->content_url);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path)
            : \App\Support\Embed::poster($this->content_url);
    }

    public function instructorList(): string
    {
        return implode(', ', array_filter((array) $this->instructors));
    }

    protected static function booted(): void
    {
        static::creating(function (self $course) {
            if (blank($course->token)) {
                $course->token = (string) Str::ulid();
            }
        });
    }
}
