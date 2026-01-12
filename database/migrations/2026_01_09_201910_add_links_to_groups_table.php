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
            $table->string('whatsapp_link')->nullable()->after('description');
            $table->string('slack_link')->nullable()->after('whatsapp_link');
            $table->string('repo_link')->nullable()->after('slack_link');
            $table->string('service_link')->nullable()->after('repo_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_link', 'slack_link', 'repo_link', 'service_link']);
        });
    }
};
