<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['about', 'links', 'contact', 'social', 'newsletter', 'custom']);
            $table->longText('content')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_sections');
    }
};
