<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->nullable();
            $table->enum('page_type', ['static', 'page', 'blog', 'blog_category', 'service', 'gallery', 'custom']);
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('route_path', 500)->nullable();
            $table->string('title')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->decimal('priority', 2, 1)->default(0.8);
            $table->enum('change_frequency', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_type', 'related_id']);
            $table->index(['page_key', 'status']);
            $table->index(['route_path', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }
};
