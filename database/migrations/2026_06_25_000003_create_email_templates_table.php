<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject')->nullable();
            $table->enum('type', [
                'contact_reply',
                'lead_reply',
                'newsletter_welcome',
                'backup_success',
                'backup_failed',
                'maintenance_alert',
                'admin_alert',
                'password_reset',
                'custom',
            ])->default('custom')->index();
            $table->longText('body');
            $table->json('available_variables')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
