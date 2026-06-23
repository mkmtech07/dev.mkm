<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name')->nullable();
            $table->string('primary_color', 20)->nullable();
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->string('body_bg_color', 20)->nullable();
            $table->string('text_color', 20)->nullable();
            $table->string('heading_color', 20)->nullable();
            $table->string('link_color', 20)->nullable();
            $table->string('link_hover_color', 20)->nullable();
            $table->string('button_bg_color', 20)->nullable();
            $table->string('button_text_color', 20)->nullable();
            $table->string('button_hover_bg_color', 20)->nullable();
            $table->string('button_hover_text_color', 20)->nullable();
            $table->string('header_bg_color', 20)->nullable();
            $table->string('header_text_color', 20)->nullable();
            $table->string('header_link_color', 20)->nullable();
            $table->string('header_link_hover_color', 20)->nullable();
            $table->string('footer_bg_color', 20)->nullable();
            $table->string('footer_text_color', 20)->nullable();
            $table->string('footer_link_color', 20)->nullable();
            $table->string('footer_link_hover_color', 20)->nullable();
            $table->string('font_family')->nullable();
            $table->string('heading_font_family')->nullable();
            $table->string('font_size', 20)->nullable();
            $table->string('container_width', 20)->nullable();
            $table->string('border_radius', 20)->nullable();
            $table->string('button_radius', 20)->nullable();
            $table->enum('layout_style', ['full_width', 'boxed'])->default('full_width');
            $table->enum('header_style', ['light', 'dark', 'transparent', 'custom'])->default('light');
            $table->enum('footer_style', ['light', 'dark', 'custom'])->default('dark');
            $table->enum('theme_mode', ['light', 'dark', 'auto'])->default('light');
            $table->longText('custom_css')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
