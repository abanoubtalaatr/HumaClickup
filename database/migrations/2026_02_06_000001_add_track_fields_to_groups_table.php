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
            if (!Schema::hasColumn('groups', 'track_id')) {
                $table->foreignId('track_id')->nullable()->after('workspace_id')->constrained('tracks')->nullOnDelete();
            }
            if (!Schema::hasColumn('groups', 'min_members')) {
                $table->integer('min_members')->default(3)->after('description');
            }
            if (!Schema::hasColumn('groups', 'max_members')) {
                $table->integer('max_members')->default(5)->after('min_members');
            }
            if (!Schema::hasColumn('groups', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('max_members');
            }
        });
        
        Schema::table('groups', function (Blueprint $table) {
            if (!$this->indexExists('groups', 'groups_track_id_index')) {
                $table->index('track_id');
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
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropIndex(['track_id']);
            $table->dropColumn(['track_id', 'min_members', 'max_members', 'is_active']);
        });
    }
};
