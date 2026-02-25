<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_question_id')->constrained()->onDelete('cascade');
            $table->string('option_text');
            $table->decimal('value', 5, 2)->nullable()->comment('Numeric value for scoring');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->index('feedback_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_question_options');
    }
};
