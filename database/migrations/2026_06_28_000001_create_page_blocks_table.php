<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('block_key')->nullable();
            $table->enum('type', [
                'hero',
                'text',
                'image',
                'text_image',
                'services',
                'gallery',
                'testimonials',
                'faq',
                'blog',
                'cta',
                'features',
                'pricing',
                'contact_form',
                'newsletter',
                'custom_html',
            ]);
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('background_image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 500)->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_url', 500)->nullable();
            $table->string('background_color', 20)->nullable();
            $table->string('text_color', 20)->nullable();
            $table->json('settings')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_id', 'status', 'sort_order']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
