<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostImage extends Model
{
    protected $fillable = ['post_id', 'file_path', 'thumb_path', 'sort_order'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function url(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function thumbUrl(): ?string
    {
        return $this->thumb_path ? Storage::disk('public')->url($this->thumb_path) : $this->url();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $image) {
            foreach ([$image->file_path, $image->thumb_path] as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }
}
