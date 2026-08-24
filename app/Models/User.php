<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Training;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'slug', 'photo_path', 'banner_path',
    'bio', 'phone', 'city', 'classification', 'profile_public',
    'is_first_year', 'first_year_ends_at', 'email_opt_out',
    'height', 'weight', 'years_experience',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Hardcoded door-gate for /admin.
     *
     * This decides who may reach the panel AT ALL, and is deliberately not
     * data-driven -- a bad edit in section_access must never be able to lock
     * everyone out. SectionAccess then decides what they see once inside.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(Roles::PANEL);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class)->latest('created_at');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class)->latest('issued_at');
    }

    public function courseCompletions(): HasMany
    {
        return $this->hasMany(CourseCompletion::class);
    }

    /** Sees the training half of the dashboard. */
    public function isTrainingMember(): bool
    {
        return $this->hasAnyRole(Roles::TRAINING);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function bannerUrl(): ?string
    {
        return $this->banner_path ? Storage::disk('public')->url($this->banner_path) : null;
    }

    /** Fallback avatar when no photo is uploaded. */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first . $last) ?: '?';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (self $user) {
            if (filled($user->slug) || blank($user->name)) {
                return;
            }

            $base = Str::slug($user->name) ?: 'official';
            $slug = $base;
            $n    = 1;

            while (static::query()->where('slug', $slug)->whereKeyNot($user->getKey())->exists()) {
                $slug = $base . '-' . (++$n);
            }

            $user->slug = $slug;
        });
    }

    /** Approved training hours for a season, defaulting to the current one. */
    public function cifHours(?string $seasonLabel = null): float
    {
        return Training::cifHours($this, $seasonLabel);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'profile_public'     => 'boolean',
            'is_first_year'      => 'boolean',
            'email_opt_out'      => 'boolean',
            'first_year_ends_at' => 'date',
        ];
    }
}
