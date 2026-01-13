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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable(); // Null if timer is running
            $table->integer('duration')->nullable(); // Calculated duration in seconds
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_manual')->default(false); // Manual entry vs timer
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index('task_id');
            $table->index('user_id');
            $table->index('start_time');
            $table->index(['user_id', 'end_time']); // For active timers
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
