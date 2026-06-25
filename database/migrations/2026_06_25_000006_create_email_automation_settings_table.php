<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_automation_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('contact_auto_reply')->default(true);
            $table->boolean('contact_admin_alert')->default(true);
            $table->boolean('lead_auto_reply')->default(true);
            $table->boolean('lead_admin_alert')->default(true);
            $table->boolean('newsletter_welcome')->default(true);
            $table->boolean('backup_success_alert')->default(true);
            $table->boolean('backup_failed_alert')->default(true);
            $table->boolean('maintenance_alert')->default(true);
            $table->string('admin_email')->nullable();
            $table->string('cc_email')->nullable();
            $table->string('bcc_email')->nullable();
            $table->boolean('queue_emails')->default(false);
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_automation_settings');
    }
};
