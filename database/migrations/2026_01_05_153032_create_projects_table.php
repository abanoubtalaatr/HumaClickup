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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('space_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('default_assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('automation_rules')->nullable(); // Future: automation configuration
            $table->foreignId('template_id')->nullable()->constrained('projects')->onDelete('set null'); // Self-referencing for templates
            $table->string('progress_calculation_method')->default('status'); // status, count, time
            $table->decimal('progress', 5, 2)->default(0); // 0-100
            $table->boolean('is_archived')->default(false);
            $table->integer('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index('space_id');
            $table->index(['workspace_id', 'is_archived']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
