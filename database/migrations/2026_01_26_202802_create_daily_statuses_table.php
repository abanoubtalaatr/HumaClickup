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
        Schema::create('daily_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Creator of the status');
            $table->date('date');
            $table->text('status')->comment('What the user did today');
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index('user_id');
            $table->index('date');
            // Ensure one status per user per day per workspace
            $table->unique(['workspace_id', 'user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_statuses');
    }
};
