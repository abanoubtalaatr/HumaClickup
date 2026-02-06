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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('workspace_id')->constrained()->nullOnDelete();
            $table->decimal('completed_hours', 5, 2)->default(0)->after('status')->comment('Hours completed on that day');
            $table->boolean('checked_by_mentor')->default(false)->after('completed_hours');
            $table->foreignId('mentor_id')->nullable()->after('checked_by_mentor')->constrained('users')->nullOnDelete();
            $table->timestamp('mentor_checked_at')->nullable()->after('mentor_id');
            $table->enum('auto_marked', ['yes', 'no'])->default('no')->after('mentor_checked_at')->comment('Auto-marked based on task completion');
            
            $table->index('project_id');
            $table->index('checked_by_mentor');
            $table->index('mentor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['mentor_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['checked_by_mentor']);
            $table->dropIndex(['mentor_id']);
            $table->dropColumn(['project_id', 'completed_hours', 'checked_by_mentor', 'mentor_id', 'mentor_checked_at', 'auto_marked']);
        });
    }
};
