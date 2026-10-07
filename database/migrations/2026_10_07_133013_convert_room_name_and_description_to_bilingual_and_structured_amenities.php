<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Room name and description become {en, ru} pairs, like hotel_name and
     * section_headings, so a room's own category title and blurb render in
     * the guest's chosen language instead of always English.
     *
     * Furniture moves from two free-text, auto-translated lists (furniture /
     * furniture_ru) to a single `amenities` array of fixed canonical keys
     * (e.g. "double_bed", "tv") — a small, known vocabulary the frontend maps
     * to a minimalist icon + a bilingual label, rather than arbitrary text
     * that needs translating. has_tv/has_air_conditioning fold into it too
     * (as the "tv"/"ac" keys), so there's one source of truth, not two.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->json('name_bilingual')->nullable()->after('name');
            $table->json('description_bilingual')->nullable()->after('description');
            $table->json('amenities')->nullable()->after('furniture_ru');
        });

        // No doctrine/dbal in this project, so a column's type can't be
        // changed in place — swap into a new column instead, preserving
        // whatever the admin already typed as the English value.
        DB::table('rooms')->select('id', 'name', 'description')->get()->each(function (object $room): void {
            DB::table('rooms')->where('id', $room->id)->update([
                'name_bilingual' => json_encode(['en' => $room->name, 'ru' => null]),
                'description_bilingual' => json_encode(['en' => $room->description, 'ru' => null]),
            ]);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn(['name', 'description', 'furniture', 'furniture_ru', 'has_tv', 'has_air_conditioning']);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->renameColumn('name_bilingual', 'name');
            $table->renameColumn('description_bilingual', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->text('description_en')->nullable()->after('description');
            $table->json('furniture')->nullable()->after('amenities');
            $table->json('furniture_ru')->nullable()->after('furniture');
            $table->boolean('has_tv')->default(false)->after('furniture_ru');
            $table->boolean('has_air_conditioning')->default(false)->after('has_tv');
        });

        DB::table('rooms')->select('id', 'name', 'description')->get()->each(function (object $room): void {
            DB::table('rooms')->where('id', $room->id)->update([
                'name_en' => json_decode($room->name, true)['en'] ?? null,
                'description_en' => json_decode($room->description, true)['en'] ?? null,
            ]);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn(['name', 'description', 'amenities']);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->renameColumn('name_en', 'name');
            $table->renameColumn('description_en', 'description');
        });
    }
};
