<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * bed_type becomes a {en, ru} pair, like name and description before it,
     * so a Russian-only admin sees "Двуспальная кровать" instead of "Double
     * bed" in the dashboard, and an English-speaking guest still sees the
     * English label regardless of what the admin's own UI is set to.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->json('bed_type_bilingual')->nullable()->after('bed_type');
        });

        // No doctrine/dbal in this project, so a column's type can't be
        // changed in place — see the identical note in the migration that
        // converted name/description the same way.
        DB::table('rooms')->select('id', 'bed_type')->get()->each(function (object $room): void {
            DB::table('rooms')->where('id', $room->id)->update([
                'bed_type_bilingual' => json_encode(['en' => $room->bed_type, 'ru' => null]),
            ]);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('bed_type');
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->renameColumn('bed_type_bilingual', 'bed_type');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->string('bed_type_en')->nullable()->after('bed_type');
        });

        DB::table('rooms')->select('id', 'bed_type')->get()->each(function (object $room): void {
            DB::table('rooms')->where('id', $room->id)->update([
                'bed_type_en' => json_decode($room->bed_type, true)['en'] ?? null,
            ]);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('bed_type');
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->renameColumn('bed_type_en', 'bed_type');
        });
    }
};
