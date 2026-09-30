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
        Schema::table('study_materials', function (Blueprint $table) {
            $table->longText('description_rich')->nullable()->after('description');
            $table->longText('book_structure')->nullable()->after('description_rich');
            $table->longText('other_information')->nullable()->after('book_structure');
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            $table->string('edition')->nullable()->after('discount_price');
            $table->string('set_of')->nullable()->after('edition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropColumn(['description_rich', 'book_structure', 'other_information', 'discount_price', 'edition', 'set_of']);
        });
    }
};
