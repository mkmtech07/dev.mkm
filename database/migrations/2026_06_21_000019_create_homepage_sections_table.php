<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('section_key')->nullable();
            $table->enum('type', ['hero', 'about', 'services', 'gallery', 'testimonials', 'blog', 'faq', 'cta', 'custom']);
            $table->longText('content')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 500)->nullable();
            $table->string('image')->nullable();
            $table->string('background_image')->nullable();
            $table->string('background_color', 20)->nullable();
            $table->string('text_color', 20)->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index('section_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
