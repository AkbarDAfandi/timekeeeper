<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days_of_week');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_override')->default(false);
            $table->foreignId('global_preset_id')->nullable()->constrained('global_presets');
            $table->foreignId('tenant_template_id')->nullable()->constrained('templates');
            $table->string('custom_text_addition')->nullable();
            $table->string('cached_audio_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
