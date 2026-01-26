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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Creator of the topic');
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('set null')->comment('Track for filtering');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('presentation_link')->nullable();
            $table->date('date');
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
            
            $table->index('workspace_id');
            $table->index('user_id');
            $table->index('track_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
