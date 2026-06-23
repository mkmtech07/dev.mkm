<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('note');
            $table->enum('note_type', ['general', 'call', 'email', 'whatsapp', 'meeting', 'follow_up'])->default('general');
            $table->dateTime('next_follow_up_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_notes');
    }
};
