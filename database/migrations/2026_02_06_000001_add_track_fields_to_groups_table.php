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
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('track_id')->nullable()->after('workspace_id')->constrained('tracks')->nullOnDelete();
            $table->integer('min_members')->default(3)->after('description');
            $table->integer('max_members')->default(5)->after('min_members');
            $table->boolean('is_active')->default(true)->after('max_members');
            
            $table->index('track_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropIndex(['track_id']);
            $table->dropColumn(['track_id', 'min_members', 'max_members', 'is_active']);
        });
    }
};
