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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('current_week_hours', 5, 2)->default(0)->after('email_verified_at')->comment('Hours logged in current week');
            $table->date('week_start_date')->nullable()->after('current_week_hours')->comment('Start date of current week tracking');
            $table->boolean('meets_weekly_target')->default(false)->after('week_start_date')->comment('Whether user met 30 hours weekly target');
            
            $table->index('meets_weekly_target');
            $table->index('week_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['meets_weekly_target']);
            $table->dropIndex(['week_start_date']);
            $table->dropColumn(['current_week_hours', 'week_start_date', 'meets_weekly_target']);
        });
    }
};
