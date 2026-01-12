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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('list_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('status_id')->constrained('custom_statuses')->onDelete('restrict');
            $table->foreignId('creator_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade'); // For sub-tasks
            
            $table->string('title');
            $table->longText('description')->nullable(); // Rich text HTML
            $table->string('priority')->default('normal'); // urgent, high, normal, low, none
            $table->dateTime('due_date')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->integer('estimated_time')->nullable(); // Minutes
            $table->integer('position')->default(0); // For drag-drop ordering
            $table->decimal('completion_percentage', 5, 2)->default(0); // 0-100
            $table->json('recurring_settings')->nullable(); // Future: recurring task config
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_private')->default(false); // Private tasks override project permissions
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index('project_id');
            $table->index('status_id');
            $table->index(['project_id', 'status_id', 'position']);
            $table->index('parent_id');
            $table->index('due_date');
            
            // Fulltext index only for MySQL
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description']); // For search
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
