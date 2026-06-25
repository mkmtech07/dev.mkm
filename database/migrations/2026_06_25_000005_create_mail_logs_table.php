<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient')->nullable()->index();
            $table->string('subject')->nullable();
            $table->string('template_slug')->nullable()->index();
            $table->string('mail_type')->nullable()->index();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->json('data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['mail_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
