<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Purpose: Support multiple guests from different tracks per project.
     * Replaces the single group_id relationship with flexible many-to-many.
     */
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['guest', 'tester', 'mentor'])->default('guest');
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('set null')
                ->comment('Track for guests (e.g., Frontend, Backend, Testing, UI/UX)');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['project_id', 'user_id', 'role']);
            
            // Indexes for queries
            $table->index('project_id');
            $table->index('user_id');
            $table->index(['project_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
