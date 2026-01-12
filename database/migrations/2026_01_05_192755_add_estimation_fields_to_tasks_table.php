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
            $table->integer('estimated_minutes')->nullable()->after('time_estimate')->comment('Final/average estimated time in minutes');
            $table->enum('estimation_status', ['pending', 'polling', 'completed'])->default('pending')->after('estimated_minutes')->comment('Status of estimation polling');
            $table->timestamp('estimation_completed_at')->nullable()->after('estimation_status');
            $table->foreignId('estimation_edited_by')->nullable()->after('estimation_completed_at')->constrained('users')->onDelete('set null')->comment('Member who edited the estimation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['estimation_edited_by']);
            $table->dropColumn(['estimated_minutes', 'estimation_status', 'estimation_completed_at', 'estimation_edited_by']);
        });
    }
};
