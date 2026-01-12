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
        Schema::create('custom_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7); // Hex color
            $table->string('type')->default('todo'); // todo, in_progress, done, archived
            $table->integer('order')->default(0);
            $table->decimal('progress_contribution', 5, 2)->default(0); // 0-100, how much this status contributes to project progress
            $table->boolean('is_default')->default(false); // First status for new tasks
            $table->timestamps();
            
            $table->index(['project_id', 'order']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_statuses');
    }
};
