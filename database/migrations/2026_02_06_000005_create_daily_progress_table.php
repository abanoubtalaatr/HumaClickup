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
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('completed_tasks_count')->default(0)->comment('Number of tasks marked done on this day');
            $table->decimal('total_hours', 5, 2)->default(0)->comment('Total hours logged on this day');
            $table->decimal('progress_percentage', 5, 2)->default(0)->comment('Daily progress percentage (0-100)');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'project_id', 'date']);
            $table->index(['workspace_id', 'date']);
            $table->index(['project_id', 'date']);
            $table->index('user_id');
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
