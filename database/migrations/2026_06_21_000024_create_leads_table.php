<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('company_name')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('source', ['contact_form', 'quote_request', 'service_enquiry', 'phone_call', 'whatsapp', 'manual', 'other'])->default('manual');
            $table->enum('status', ['new', 'contacted', 'follow_up', 'interested', 'converted', 'not_interested', 'spam', 'closed'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('budget', 100)->nullable();
            $table->enum('preferred_contact_method', ['phone', 'email', 'whatsapp', 'any'])->nullable();
            $table->dateTime('follow_up_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('status_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority', 'created_at']);
            $table->index(['follow_up_date', 'status_active']);
            $table->index(['source', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
