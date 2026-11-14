<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question_hash', 64)->nullable()->index();
        });

        DB::table('questions')
            ->select(['id', 'question_text'])
            ->orderBy('id')
            ->chunkById(500, function ($questions): void {
                foreach ($questions as $question) {
                    $normalized = mb_strtolower($question->question_text, 'UTF-8');
                    $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $normalized);
                    $normalized = trim(preg_replace('/\s+/u', ' ', $normalized ?? ''));

                    if ($normalized !== '') {
                        DB::table('questions')
                            ->where('id', $question->id)
                            ->update(['question_hash' => hash('sha256', $normalized)]);
                    }
                }
            });

        Schema::table('pdf_extractions', function (Blueprint $table) {
            $table->foreignId('examination_id')->nullable()->after('topic_id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('ai_generations', function (Blueprint $table) {
            $table->foreignId('examination_id')->nullable()->after('topic_id')
                ->constrained()->nullOnDelete();
            $table->string('language')->default('English')->after('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->dropForeign(['examination_id']);
            $table->dropColumn(['examination_id', 'language']);
        });

        Schema::table('pdf_extractions', function (Blueprint $table) {
            $table->dropForeign(['examination_id']);
            $table->dropColumn('examination_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['question_hash']);
            $table->dropColumn('question_hash');
        });
    }
};
