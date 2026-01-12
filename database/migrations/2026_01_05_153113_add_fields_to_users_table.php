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
            $table->string('avatar')->nullable()->after('email');
            $table->string('timezone')->default('UTC')->after('avatar');
            $table->string('locale', 10)->default('en')->after('timezone');
            $table->json('preferences')->nullable()->after('locale'); // User preferences
            $table->timestamp('last_activity_at')->nullable()->after('preferences');
            $table->string('status')->default('active')->after('last_activity_at'); // active, suspended, deleted
            $table->softDeletes()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'timezone',
                'locale',
                'preferences',
                'last_activity_at',
                'status',
                'deleted_at'
            ]);
        });
    }
};
