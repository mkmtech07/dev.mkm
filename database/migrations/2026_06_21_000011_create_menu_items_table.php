<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('title');
            $table->string('type');
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->text('url')->nullable();
            $table->string('icon')->nullable();
            $table->string('target')->default('_self');
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'status', 'sort_order'], 'menu_items_tree_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
