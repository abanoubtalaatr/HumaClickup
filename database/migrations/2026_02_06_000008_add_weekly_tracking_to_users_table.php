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
            if (!Schema::hasColumn('users', 'current_week_hours')) {
                $table->decimal('current_week_hours', 5, 2)->default(0)->after('email_verified_at')->comment('Hours logged in current week');
            }
            if (!Schema::hasColumn('users', 'week_start_date')) {
                $table->date('week_start_date')->nullable()->after('current_week_hours')->comment('Start date of current week tracking');
            }
            if (!Schema::hasColumn('users', 'meets_weekly_target')) {
                $table->boolean('meets_weekly_target')->default(false)->after('week_start_date')->comment('Whether user met 30 hours weekly target');
            }
        });
        
        // Add indexes separately
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_meets_weekly_target_index')) {
                $table->index('meets_weekly_target');
            }
            if (!$this->indexExists('users', 'users_week_start_date_index')) {
                $table->index('week_start_date');
            }
        });
    }

    private function indexExists($table, $name): bool
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->introspectTable($table);
        return $doctrineTable->hasIndex($name);
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
