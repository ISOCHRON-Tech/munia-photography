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
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // File paths
            $table->string('original_path');          // storage/app/private/originals/{uuid}
            $table->string('webp_path')->nullable();   // public optimised WebP
            $table->string('avif_path')->nullable();   // public optimised AVIF
            $table->json('srcset_paths')->nullable();  // responsive sizes map

            // Placeholders
            $table->string('blurhash')->nullable();
            $table->string('lqip_path')->nullable();   // tiny base64 or file path

            // Image dimensions
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();

            // EXIF metadata
            $table->string('camera_make')->nullable();
            $table->string('camera_model')->nullable();
            $table->string('lens')->nullable();
            $table->string('iso')->nullable();
            $table->string('aperture')->nullable();
            $table->string('shutter_speed')->nullable();
            $table->string('focal_length')->nullable();
            $table->string('taken_at_location')->nullable();
            $table->dateTime('taken_at')->nullable();

            // Relations
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // Status
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
