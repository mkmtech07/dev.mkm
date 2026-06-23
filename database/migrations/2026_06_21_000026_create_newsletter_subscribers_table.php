<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->enum('source', ['footer', 'popup', 'contact_page', 'blog', 'manual', 'api', 'other'])->default('footer');
            $table->enum('status', ['subscribed', 'unsubscribed', 'pending', 'blocked'])->default('subscribed');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_token', 64)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->boolean('status_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'source', 'created_at']);
            $table->index(['status_active', 'subscribed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
