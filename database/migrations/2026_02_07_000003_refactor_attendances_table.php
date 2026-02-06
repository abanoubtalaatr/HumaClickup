<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Purpose: Refactor attendances to:
     * - Derive status from daily_progress (not manual entry)
     * - Support mentor approval
     * - Link to daily_progress record
     * - Follow the rule: no work = no attendance
     */
    public function up(): void
    {
        // Drop existing table if it exists (clean slate)
        Schema::dropIfExists('attendances');
        
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')
                ->comment('Guest who this attendance belongs to');
            $table->date('date')->comment('The specific day');
            
            // Link to daily progress
            $table->foreignId('daily_progress_id')->nullable()->constrained('daily_progress')->onDelete('cascade')
                ->comment('Link to the progress record that determined this attendance');
            
            // Derived status (NEVER set manually)
            $table->enum('status', ['present', 'absent'])->default('absent')
                ->comment('Derived: present if progress >= 100%, else absent');
            
            // Approval workflow
            $table->boolean('approved')->default(false)
                ->comment('Has mentor approved this attendance?');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Mentor who approved');
            $table->timestamp('approved_at')->nullable();
            
            // Metadata
            $table->text('notes')->nullable()->comment('Optional mentor notes');
            
            $table->timestamps();
            
            // Ensure one attendance record per guest per day per project
            $table->unique(['user_id', 'project_id', 'date']);
            
            // Indexes
            $table->index(['project_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['status', 'date']);
            $table->index(['approved', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
