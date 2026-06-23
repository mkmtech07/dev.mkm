<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schema_markups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Organization', 'LocalBusiness', 'Website', 'Breadcrumb', 'FAQ', 'Article', 'Product', 'Custom']);
            $table->longText('schema_json')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_markups');
    }
};
