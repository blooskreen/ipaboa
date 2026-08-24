<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use App\Models\Season;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    public const CATEGORIES = [
        'Weekly Zoom Meetings',
        'General Body Meeting',
        'Training Camps Attended',
        'Scrimmage',
        'First Year',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $i => $name) {
            CourseCategory::firstOrCreate(
                ['name' => $name],
                ['sort_order' => ($i + 1) * 10],
            );
        }

        if (Season::query()->doesntExist()) {
            Season::create([
                'label'      => '2026-27',
                'is_current' => true,
                'started_at' => now()->startOfDay(),
            ]);
        }
    }
}
