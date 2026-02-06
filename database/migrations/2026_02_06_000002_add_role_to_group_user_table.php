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
        Schema::table('group_user', function (Blueprint $table) {
            $table->enum('role', ['leader', 'member'])->default('member')->after('user_id');
            $table->foreignId('assigned_by_user_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropForeign(['assigned_by_user_id']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'assigned_by_user_id']);
        });
    }
};
