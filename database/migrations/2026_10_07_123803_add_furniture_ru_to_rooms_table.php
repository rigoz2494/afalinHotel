<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable, filled either by the admin or by auto-translation at seed
     * time. The frontend falls back to the English `furniture` list until
     * it's set — the same convention as `question_ru`/`answer_ru` on faqs.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->json('furniture_ru')->nullable()->after('furniture');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('furniture_ru');
        });
    }
};
