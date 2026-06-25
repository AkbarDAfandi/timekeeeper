<?php

namespace Database\Seeders;

use App\Models\ScheduleCategory;
use Illuminate\Database\Seeder;

class ScheduleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'class', 'name' => 'Class', 'color' => '#3B82F6'],
            ['slug' => 'break', 'name' => 'Break', 'color' => '#F97316'],
            ['slug' => 'flag-ceremony', 'name' => 'Flag Ceremony', 'color' => '#EF4444'],
            ['slug' => 'extracurricular', 'name' => 'Extracurricular', 'color' => '#A855F7'],
            ['slug' => 'other', 'name' => 'Other', 'color' => '#6B7280'],
        ];

        foreach ($categories as $cat) {
            ScheduleCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
