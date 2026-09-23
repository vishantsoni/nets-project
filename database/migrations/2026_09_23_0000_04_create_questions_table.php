<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('topic_id')->nullable()->constrained()->onDelete('set null');
            $table->string('difficulty')->default('medium');
            $table->integer('marks')->default(1);
            $table->integer('negative_marks')->default(0);
            $table->text('explanation')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_ai_generated')->default(false);
            $table->text('correct_answer_text')->nullable();
            $table->foreignId('institute_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['question_type', 'status']);
            $table->index(['subject_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};