<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description');
            $table->string('image')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->unsignedInteger('years_of_experience')->nullable();
            $table->unsignedInteger('projects_completed')->nullable();
            $table->unsignedInteger('clients_served')->nullable();
            $table->unsignedInteger('team_members')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_sections');
    }
};
