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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->json('settings')->nullable(); // Workspace-level settings
            $table->string('billing_status')->default('active'); // active, suspended, cancelled
            $table->bigInteger('storage_limit')->default(10737418240); // 10GB in bytes
            $table->bigInteger('storage_used')->default(0);
            $table->integer('member_capacity')->nullable(); // null = unlimited
            $table->json('custom_branding')->nullable(); // logo, colors, etc.
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('owner_id');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
