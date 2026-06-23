<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('sitemap_status')->default(true);
            $table->boolean('robots_status')->default(true);
            $table->boolean('schema_status')->default(true);
            $table->boolean('default_robots_index')->default(true);
            $table->boolean('default_robots_follow')->default(true);
            $table->unsignedInteger('sitemap_cache_minutes')->default(60);
            $table->longText('robots_content')->nullable();
            $table->string('google_analytics_id', 100)->nullable();
            $table->string('google_tag_manager_id', 100)->nullable();
            $table->string('google_search_console_code', 500)->nullable();
            $table->string('facebook_pixel_id', 100)->nullable();
            $table->longText('custom_head_code')->nullable();
            $table->longText('custom_body_code')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
