<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->unique()->constrained('exam_attempts')->onDelete('cascade');
            $table->integer('total_questions')->default(0);
            $table->integer('attempted_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('incorrect_answers')->default(0);
            $table->integer('unanswered_questions')->default(0);
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->decimal('obtained_marks', 8, 2)->default(0);
            $table->decimal('percentage', 8, 2)->default(0);
            $table->integer('time_taken')->default(0)->comment('Seconds');
            $table->integer('rank_position')->nullable();
            $table->text('subject_wide_performance')->nullable();
            $table->text('topic_wide_performance')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};