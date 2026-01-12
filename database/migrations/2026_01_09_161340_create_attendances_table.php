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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('checked_in_at')->nullable();
            $table->time('checked_out_at')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'early_leave'])->default('absent');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['workspace_id', 'guest_id', 'date']);
            $table->index(['workspace_id', 'date']);
            $table->index(['guest_id', 'date']);
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
