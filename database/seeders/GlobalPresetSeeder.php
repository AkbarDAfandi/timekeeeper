<?php

namespace Database\Seeders;

use App\Models\Global_presets;
use Illuminate\Database\Seeder;

class GlobalPresetSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            ['name' => 'Jam Ke-1', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe1.mp3'],
            ['name' => 'Jam Ke-2', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe2.mp3'],
            ['name' => 'Jam Ke-3', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe3.mp3'],
            ['name' => 'Jam Ke-4', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe4.mp3'],
            ['name' => 'Jam Ke-5', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe5.mp3'],
            ['name' => 'Jam Ke-6', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe6.mp3'],
            ['name' => 'Jam Ke-7', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe7.mp3'],
            ['name' => 'Jam Ke-8', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe8.mp3'],
            ['name' => 'Jam Ke-9', 'category' => 'bell', 'audio_path' => 'global/presets/JamKe9.mp3'],
            ['name' => 'Istirahat Ke-1', 'category' => 'jingle', 'audio_path' => 'global/presets/IstirahatKe1.mp3'],
            ['name' => 'Istirahat Ke-2', 'category' => 'jingle', 'audio_path' => 'global/presets/IstirahatKe2.mp3'],
            ['name' => 'Bel Pulang', 'category' => 'chime', 'audio_path' => 'global/presets/BelPulang.mp3'],
        ];

        foreach ($presets as $preset) {
            Global_presets::create($preset);
        }
    }
}
