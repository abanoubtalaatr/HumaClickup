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
        Schema::create('pull_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Guest who submitted the PR');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('set null');
            $table->string('link', 2048)->comment('Pull request URL');
            $table->date('date');
            $table->timestamps();

            $table->index(['workspace_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['project_id', 'date']);
            $table->index(['track_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pull_requests');
    }
};
