<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['subject_id', 'name']);
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->after('subject_id')->constrained()->cascadeOnDelete();
        });

        $now = now();

        foreach (DB::table('topics')->select('subject_id')->distinct()->get() as $topicGroup) {
            $chapterId = DB::table('chapters')->insertGetId([
                'subject_id' => $topicGroup->subject_id,
                'name' => 'General',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('topics')
                ->where('subject_id', $topicGroup->subject_id)
                ->update(['chapter_id' => $chapterId]);
        }

        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->unsignedBigInteger('chapter_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::table('topics')
            ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
            ->update(['topics.subject_id' => DB::raw('chapters.subject_id')]);

        Schema::table('topics', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable(false)->change();
            $table->dropForeign(['chapter_id']);
            $table->dropColumn('chapter_id');
        });

        Schema::dropIfExists('chapters');
    }
};
