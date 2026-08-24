<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaItem extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'user_id', 'type', 'file_path', 'thumb_path', 'video_url',
        'caption', 'taken_on', 'is_public', 'sort_order',
    ];

    protected $casts = [
        'taken_on'   => 'date',
        'is_public'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function url(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    /** Square crop for grid tiles; falls back to the video poster. */
    public function thumbUrl(): ?string
    {
        if ($this->thumb_path) {
            return Storage::disk('public')->url($this->thumb_path);
        }

        if ($this->isVideo()) {
            return $this->posterUrl();
        }

        return $this->url();
    }

    public function embedUrl(): ?string
    {
        return \App\Support\Embed::url($this->video_url);
    }

    public function posterUrl(): ?string
    {
        return \App\Support\Embed::poster($this->video_url);
    }

    /** Delete the files too, so storage does not fill with orphans. */
    protected static function booted(): void
    {
        static::deleting(function (self $item) {
            foreach ([$item->file_path, $item->thumb_path] as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }
}
