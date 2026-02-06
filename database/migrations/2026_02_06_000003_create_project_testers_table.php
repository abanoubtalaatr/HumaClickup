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
        Schema::create('project_testers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('tester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->enum('status', ['pending', 'active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['project_id', 'tester_id']);
            $table->index('project_id');
            $table->index('tester_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_testers');
    }
};
