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
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('type', ['task', 'bug'])->default('task')->after('title');
            $table->foreignId('related_task_id')->nullable()->after('parent_id')->constrained('tasks')->onDelete('set null')->comment('Task this bug is related to');
            $table->index('type');
            $table->index('related_task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['related_task_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['related_task_id']);
            $table->dropColumn(['type', 'related_task_id']);
        });
    }
};
