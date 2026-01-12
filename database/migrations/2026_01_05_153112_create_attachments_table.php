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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // task, comment, project, etc.
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Uploader
            $table->string('filename'); // Stored filename (UUID)
            $table->string('original_filename');
            $table->bigInteger('size'); // Bytes
            $table->string('mime_type');
            $table->string('disk')->default('local'); // local, s3
            $table->string('path'); // Storage path
            $table->string('thumbnail_path')->nullable(); // For images
            $table->timestamps();
            
            // morphs() already creates index for attachable_type and attachable_id
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
