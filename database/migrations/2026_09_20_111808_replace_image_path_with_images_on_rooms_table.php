<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->json('images')->nullable()->after('furniture');
        });

        DB::table('rooms')->whereNotNull('image_path')->orderBy('id')->each(function (object $room): void {
            DB::table('rooms')->where('id', $room->id)->update(['images' => json_encode([$room->image_path])]);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('furniture');
        });

        DB::table('rooms')->whereNotNull('images')->orderBy('id')->each(function (object $room): void {
            DB::table('rooms')->where('id', $room->id)->update(['image_path' => json_decode($room->images, true)[0] ?? null]);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('images');
        });
    }
};
