<?php

namespace Database\Seeders;

use App\Models\SectionAccess;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Roles::ALL as $name) {
            Role::findOrCreate($name, 'web');
        }

        // Retire roles no longer defined in code, but never one that still
        // has users attached -- that would silently strip someone's access.
        Role::whereNotIn('name', Roles::ALL)->get()->each(function (Role $role) {
            if ($role->users()->count() === 0) {
                $role->delete();
            } else {
                $this->command?->warn("Kept stale role '{$role->name}' - still has users assigned.");
            }
        });

        SectionAccess::syncDefaults();
    }
}
