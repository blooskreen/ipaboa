<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Training;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
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

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class)->latest('issued_at');
    }

    public function courseCompletions(): HasMany
    {
        return $this->hasMany(CourseCompletion::class);
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
