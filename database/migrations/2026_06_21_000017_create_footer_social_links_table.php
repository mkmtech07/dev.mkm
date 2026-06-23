<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->text('url');
            $table->string('icon')->nullable();
            $table->enum('target', ['_self', '_blank'])->default('_blank');
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_social_links');
    }
};
