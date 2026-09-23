<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('test_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('cbt');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('duration')->comment('Duration in minutes')->default(60);
            $table->integer('total_marks')->default(0);
            $table->integer('passing_marks')->default(0);
            $table->integer('max_attempts')->default(1);
            $table->boolean('negative_marking')->default(false);
            $table->decimal('negative_marks_ratio', 5, 2)->default(0);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_result_immediately')->default(true);
            $table->string('result_visibility')->default('immediately');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_published')->default(false);
            $table->foreignId('institute_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index('test_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};