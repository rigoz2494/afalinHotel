<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable, filled either by the admin or by the "Translate to Russian"
     * action. The frontend falls back to the English text until they're set.
     */
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->string('question_ru')->nullable()->after('question');
            $table->text('answer_ru')->nullable()->after('answer');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->dropColumn(['question_ru', 'answer_ru']);
        });
    }
};
