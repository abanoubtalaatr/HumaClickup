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
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->nullable(); // Hex color
            $table->string('icon')->nullable(); // Icon name or emoji
            $table->text('description')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->integer('order')->default(0);
            $table->json('access_control')->nullable(); // Override workspace permissions
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index(['workspace_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
