<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Purpose: Refactor daily_progress to support:
     * - Per-guest, per-day tracking
     * - Mentor approval workflow
     * - Link to main task
     * - Consistent HOURS unit
     */
    public function up(): void
    {
        // Drop existing table if it exists (clean slate)
        Schema::dropIfExists('daily_progress');
        
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')
                ->comment('Guest who this progress belongs to');
            $table->date('date')->comment('The specific day');
            
            // Task association
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null')
                ->comment('Main task for this day (must be >= 6 hours)');
            
            // Progress metrics (ALL IN HOURS)
            $table->decimal('required_hours', 5, 2)->default(6.00)
                ->comment('Required hours per day (default: 6)');
            $table->decimal('completed_hours', 5, 2)->default(0)
                ->comment('Actual hours completed (from main task)');
            $table->decimal('progress_percentage', 5, 2)->default(0)
                ->comment('Progress: (completed / required) × 100, capped at 100%');
            
            // Approval workflow
            $table->boolean('approved')->default(false)
                ->comment('Has mentor approved this progress?');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Mentor who approved');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Ensure one progress record per guest per day per project
            $table->unique(['user_id', 'project_id', 'date']);
            
            // Indexes
            $table->index(['project_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['approved', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_progress');
    }
};
