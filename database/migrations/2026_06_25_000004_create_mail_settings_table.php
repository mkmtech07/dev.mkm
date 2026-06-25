<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('mailer', ['smtp', 'sendmail', 'log', 'array'])->default('smtp')->index();
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->enum('encryption', ['tls', 'ssl', 'none'])->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to_address')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->unsignedInteger('timeout')->nullable()->default(30);
            $table->string('test_recipient')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
