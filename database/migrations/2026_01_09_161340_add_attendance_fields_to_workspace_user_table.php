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
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->json('attendance_days')->nullable()->after('track_id'); // ['sunday', 'monday', 'tuesday', etc.]
            $table->boolean('is_suspended')->default(false)->after('attendance_days');
            $table->integer('absence_count')->default(0)->after('is_suspended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->dropColumn(['attendance_days', 'is_suspended', 'absence_count']);
        });
    }
};
