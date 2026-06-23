<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (! Schema::hasColumn('menus', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'blog_id')) {
                $table->foreignId('blog_id')
                    ->nullable()
                    ->after('page_id')
                    ->constrained('blogs')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('menu_items', 'blog_category_id')) {
                $table->foreignId('blog_category_id')
                    ->nullable()
                    ->after('blog_id')
                    ->constrained('blog_categories')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('menu_items', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // The complete schema is also defined in the original create migrations.
    }
};
