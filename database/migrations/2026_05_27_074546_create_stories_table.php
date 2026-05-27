<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');             // Rich HTML content
            $table->string('banner_path')->nullable();
            $table->string('banner_webp_path')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image_path')->nullable();

            // Status
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->dateTime('published_at')->nullable();

            // Reading time (auto-computed on save)
            $table->unsignedSmallInteger('reading_time_minutes')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
