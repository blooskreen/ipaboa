<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /** slug => [label, colour] */
    public const CATEGORIES = [
        'question'     => ['Question',           '#1D3C87'],
        'announcement' => ['Announcement',       '#C8102E'],
        'recap'        => ['Game Recap',         '#4B2E83'],
        'rules'        => ['Rules Clarification', '#0f766e'],
        'shoutout'     => ['Shoutout',           '#C9A227'],
        'availability' => ['Availability',       '#475569'],
    ];

    public const FEELINGS = [
        'fired-up'   => 'Fired up',
        'proud'      => 'Proud',
        'grateful'   => 'Grateful',
        'focused'    => 'Focused',
        'tired'      => 'Worn out',
        'celebrating'=> 'Celebrating',
        'learning'   => 'Learning',
        'travelling' => 'On the road',
    ];

    public const FEELING_EMOJI = [
        'fired-up'    => '🔥',
        'proud'       => '🏆',
        'grateful'    => '🙏',
        'focused'     => '🎯',
        'tired'       => '😮‍💨',
        'celebrating' => '🎉',
        'learning'    => '📚',
        'travelling'  => '✈️',
    ];

    protected $fillable = ['user_id', 'body', 'category', 'feeling'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->oldest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function pollOptions(): HasMany
    {
        return $this->hasMany(PostPollOption::class)->orderBy('sort_order');
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PostPollVote::class);
    }

    public function hasPoll(): bool
    {
        return $this->pollOptions->isNotEmpty();
    }

    public function categoryLabel(): ?string
    {
        return self::CATEGORIES[$this->category][0] ?? null;
    }

    public function categoryColor(): string
    {
        return self::CATEGORIES[$this->category][1] ?? '#6b7280';
    }

    public function feelingLabel(): ?string
    {
        if (! $this->feeling) {
            return null;
        }

        $emoji = self::FEELING_EMOJI[$this->feeling] ?? '';
        $word  = self::FEELINGS[$this->feeling] ?? $this->feeling;

        return trim($emoji . ' ' . $word);
    }
}
