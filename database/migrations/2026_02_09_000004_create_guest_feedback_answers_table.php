<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_feedback_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_feedback_submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('feedback_question_id')->constrained()->onDelete('cascade');
            $table->foreignId('feedback_question_option_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating_value')->nullable()->comment('For rating-type questions');
            $table->timestamps();
            $table->unique(['guest_feedback_submission_id', 'feedback_question_id'], 'submission_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_feedback_answers');
    }
};
