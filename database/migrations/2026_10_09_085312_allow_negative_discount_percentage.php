<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Both discount_percentage columns were `unsignedTinyInteger`, which
     * can't hold a negative value at all — admins need to type e.g. -15 to
     * mark a season as a 15% markup rather than a discount (the pricing
     * formula already treats a negative percentage as a markup; the
     * column just never let one be stored). No doctrine/dbal in this
     * project, so the column is swapped rather than changed in place.
     */
    public function up(): void
    {
        $this->swap('rooms');
        $this->swap('room_prices');
    }

    public function down(): void
    {
        $this->swapBack('rooms');
        $this->swapBack('room_prices');
    }

    private function swap(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->smallInteger('discount_percentage_signed')->nullable()->after('discount_percentage');
        });

        DB::table($table)->whereNotNull('discount_percentage')->update([
            'discount_percentage_signed' => DB::raw('discount_percentage'),
        ]);

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('discount_percentage');
        });

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->renameColumn('discount_percentage_signed', 'discount_percentage');
        });
    }

    private function swapBack(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->unsignedTinyInteger('discount_percentage_unsigned')->nullable()->after('discount_percentage');
        });

        DB::table($table)->where('discount_percentage', '>=', 0)->update([
            'discount_percentage_unsigned' => DB::raw('discount_percentage'),
        ]);

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('discount_percentage');
        });

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->renameColumn('discount_percentage_unsigned', 'discount_percentage');
        });
    }
};
