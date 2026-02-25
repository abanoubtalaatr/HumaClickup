<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->index(['workspace_id', 'mentor_id', 'submitted_at'], 'gfs_workspace_mentor_submitted');
            $table->index(['guest_id', 'mentor_id', 'submitted_at'], 'gfs_guest_mentor_submitted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_feedback_submissions');
    }
};
