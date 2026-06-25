<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(false)->index();
            $table->enum('mode', ['frontend_only', 'full_site'])->default('frontend_only');
            $table->string('title')->nullable();
            $table->longText('message')->nullable();
            $table->string('image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 500)->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->text('allowed_ips')->nullable();
            $table->text('excluded_paths')->nullable();
            $table->unsignedInteger('retry_after_minutes')->default(60);
            $table->enum('meta_robots', ['index', 'noindex'])->default('noindex');
            $table->longText('custom_css')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'mode']);
            $table->index(['start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_settings');
    }
};
