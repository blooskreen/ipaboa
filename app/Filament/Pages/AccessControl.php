<?php

namespace App\Filament\Pages;

use App\Models\SectionAccess;
use App\Support\Roles;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use UnitEnum;

class AccessControl extends Page
{
    protected string $view = 'filament.pages.access-control';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    public ?array $data = [];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Settings';
    }

    /**
     * Hardcoded to Administrator + Super Administrator.
     *
     * Deliberately NOT driven by section_access: this is the page that edits
     * that table, so gating it on its own data would let a single bad save
     * lock everyone out of access control permanently.
     */
    public static function canAccess(): bool
    {
        return Auth::user()?->hasAnyRole(Roles::ALWAYS_FULL) ?? false;
    }

    /** Form field names cannot contain spaces, & or / -- slug the section. */
    protected static function key(string $section): string
    {
        return Str::slug($section, '_');
    }

    /**
     * Only roles that can actually reach /admin are offered. Granting a
     * section to a role that the door-gate rejects would be a lie in the UI.
     * Administrator and Super Administrator are excluded because they are
     * always full and cannot be revoked here.
     */
    protected static function assignableRoles(): array
    {
        return array_values(array_diff(Roles::PANEL, Roles::ALWAYS_FULL));
    }

    public function mount(): void
    {
        SectionAccess::syncDefaults();
        $this->loadState();
    }

    protected function loadState(): void
    {
        $map = SectionAccess::all()->pluck('roles', 'section')->all();
        $assignable = static::assignableRoles();

        $state = [];
        foreach (SectionAccess::SECTIONS as $section) {
            $state[static::key($section)] = array_values(
                array_intersect((array) ($map[$section] ?? []), $assignable)
            );
        }

        $this->data = $state;
    }

    public function form(Schema $schema): Schema
    {
        $options = collect(static::assignableRoles())
            ->mapWithKeys(fn (string $role) => [$role => $role])
            ->all();

        $fields = collect(SectionAccess::SECTIONS)
            ->map(fn (string $section) => CheckboxList::make(static::key($section))
                ->label($section)
                ->options($options)
                ->columns(3))
            ->all();

        return $schema
            ->components($fields)
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->action('save'),

            Action::make('resetDefaults')
                ->label('Reset to defaults')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('This replaces every section with the built-in defaults from SectionAccess::DEFAULTS.')
                ->action('resetDefaults'),
        ];
    }

    public function save(): void
    {
        $assignable = static::assignableRoles();

        foreach (SectionAccess::SECTIONS as $section) {
            $selected = (array) ($this->data[static::key($section)] ?? []);

            SectionAccess::updateOrCreate(
                ['section' => $section],
                ['roles' => array_values(array_intersect($selected, $assignable))],
            );
        }

        SectionAccess::flush();

        Notification::make()
            ->title('Access control saved')
            ->success()
            ->send();
    }

    public function resetDefaults(): void
    {
        foreach (SectionAccess::SECTIONS as $section) {
            SectionAccess::updateOrCreate(
                ['section' => $section],
                ['roles' => SectionAccess::DEFAULTS[$section] ?? []],
            );
        }

        SectionAccess::flush();
        $this->loadState();

        Notification::make()
            ->title('Reset to defaults')
            ->success()
            ->send();
    }
}
