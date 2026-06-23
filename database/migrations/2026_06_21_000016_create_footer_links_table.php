<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('footer_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('url');
            $table->string('icon')->nullable();
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['footer_section_id', 'status', 'sort_order'], 'footer_links_display_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_links');
    }
};
