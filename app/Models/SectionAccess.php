<?php

namespace App\Models;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SectionAccess extends Model
{
    protected $table = 'section_access';

    protected $fillable = ['section', 'roles'];

    protected $casts = ['roles' => 'array'];

    /** In-request cache: Filament calls allows() once per nav item per page load. */
    protected static ?array $map = null;

    public const SECTIONS = [
        'Careers',
        'Content',
        'Contact',
        'Education',
        'Evaluation & Setup',
        'Users',
        'Gallery',
        'Events',
        'Feed Posts',
        'Downloads',
        'CDC/Ratings',
        'Store',
        'Marketing',
        'Advertising',
        'First-Year Program',
        'Recruitment',
    ];

    public const DEFAULTS = [
        'Careers'            => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Content'            => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Contact'            => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Education'          => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Evaluation & Setup' => [Roles::CAMP_ADMIN],
        'Users'              => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Gallery'            => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Events'             => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Feed Posts'         => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Downloads'          => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'CDC/Ratings'        => [Roles::CAMP_ADMIN],
        'Store'              => [Roles::STORE_ADMIN],
        'Marketing'          => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Advertising'        => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'First-Year Program' => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
        'Recruitment'        => [Roles::LEADERSHIP, Roles::CAMP_ADMIN],
    ];

    public static function map(): array
    {
        if (static::$map === null) {
            static::$map = static::all()->pluck('roles', 'section')->all();
        }

        return static::$map;
    }

    public static function flush(): void
    {
        static::$map = null;
    }

    public static function allows(string $section): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(Roles::ALWAYS_FULL)) {
            return true;
        }

        $allowed = static::map()[$section] ?? [];

        return is_array($allowed) && count($allowed) > 0 && $user->hasAnyRole($allowed);
    }

    public static function syncDefaults(): void
    {
        foreach (static::SECTIONS as $section) {
            static::firstOrCreate(
                ['section' => $section],
                ['roles' => static::DEFAULTS[$section] ?? []],
            );
        }

        static::query()->whereNotIn('section', static::SECTIONS)->delete();

        static::flush();
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }
}
