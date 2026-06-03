<?php
namespace Database\Seeders;

use App\Models\Global_presets;
use App\Models\Schedule;
use App\Models\Templates;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GlobalPresetSeeder::class,
        ]);

        $tenant = Tenant::create([
            'name' => 'SMK Negeri 1 Lumajang',
        ]);

        User::create([
            'name' => 'System Superadmin',
            'email' => 'superadmin@app.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'tenant_id' => null,
        ]);

        $admin = User::create([
            'name' => 'IT Admin',
            'email' => 'admin@smkn1lmj.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'tenant_id' => $tenant->id,
        ]);

        User::create([
            'name' => 'Front Desk Operator',
            'email' => 'operator@smkn1lmj.sch.id',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'tenant_id' => $tenant->id,
        ]);

        User::create([
            'name' => 'Audio Node 01',
            'email' => 'player@smkn1lmj.sch.id',
            'password' => Hash::make('password'),
            'role' => 'player',
            'tenant_id' => $tenant->id,
        ]);

        $globalPreset = Global_presets::create([
            'name' => 'Standard School Jingle',
            'category' => 'jingle',
            'audio_path' => '/storage/global/presets/standard_jingle.mp3',
        ]);

        $template = Templates::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mulai Pelajaran',
            'content_id' => 'Waktu menunjukkan pukul {time}. Saatnya untuk {title}.',
            'content_en' => 'It is {time}. Time for {title}.',
            'is_static' => false,
            'preset_audio_path' => null,
        ]);

        Schedule::create([
            'tenant_id' => $tenant->id,
            'created_by' => $admin->id,
            'title' => 'Jam Ke-1',
            'start_time' => '07:00:00',
            'end_time' => '07:40:00',
            'days_of_week' => json_encode([1, 2, 3, 4, 5]),
            'is_active' => true,
            'is_override' => false,
            'global_preset_id' => $globalPreset->id,
            'tenant_template_id' => null,
            'custom_text_addition' => null,
            'cached_audio_path' => null,
        ]);
    }
}
