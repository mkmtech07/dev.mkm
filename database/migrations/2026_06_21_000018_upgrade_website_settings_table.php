<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('website_settings', 'tagline') && ! Schema::hasColumn('website_settings', 'site_tagline')) {
            Schema::table('website_settings', fn (Blueprint $table) => $table->renameColumn('tagline', 'site_tagline'));
        }

        if (Schema::hasColumn('website_settings', 'whatsapp_number') && ! Schema::hasColumn('website_settings', 'whatsapp')) {
            Schema::table('website_settings', fn (Blueprint $table) => $table->renameColumn('whatsapp_number', 'whatsapp'));
        }

        Schema::table('website_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('website_settings', 'white_logo')) {
                $table->string('white_logo')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'og_image')) {
                $table->string('og_image')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'primary_color')) {
                $table->string('primary_color', 20)->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'secondary_color')) {
                $table->string('secondary_color', 20)->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'google_map_embed')) {
                $table->longText('google_map_embed')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'twitter_url')) {
                $table->string('twitter_url')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'custom_css')) {
                $table->longText('custom_css')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'custom_js')) {
                $table->longText('custom_js')->nullable();
            }
            if (! Schema::hasColumn('website_settings', 'status')) {
                $table->boolean('status')->default(true);
            }
        });

        Schema::table('website_settings', function (Blueprint $table) {
            $table->string('site_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        // The complete schema is also defined in the original create migration.
    }
};
