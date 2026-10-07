<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->foreignId('pricing_period_id')->nullable()->after('room_id')
                ->constrained()->cascadeOnDelete();
        });

        // Backfill: every distinct (period_order, period_label) combo
        // becomes its own PricingPeriod row, then each room_prices row is
        // repointed at it.
        $periodIds = [];

        DB::table('room_prices')
            ->select('period_order', 'period_label')
            ->distinct()
            ->orderBy('period_order')
            ->get()
            ->each(function (object $period) use (&$periodIds): void {
                $periodIds[$period->period_order] = DB::table('pricing_periods')->insertGetId([
                    'name' => $period->period_label,
                    'sort_order' => $period->period_order,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        foreach ($periodIds as $order => $periodId) {
            DB::table('room_prices')->where('period_order', $order)->update([
                'pricing_period_id' => $periodId,
            ]);
        }

        // SQLite rebuilds the whole table for column changes/drops; each
        // concern gets its own Schema::table() call so one rebuild can't
        // clobber a constraint another step in the same batch just added.
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropUnique(['room_id', 'period_order']);
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropColumn(['period_label', 'period_order']);
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->unsignedBigInteger('pricing_period_id')->nullable(false)->change();
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->unique(['room_id', 'pricing_period_id']);
        });
    }

    public function down(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropUnique(['room_id', 'pricing_period_id']);
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->string('period_label')->nullable()->after('room_id');
            $table->unsignedSmallInteger('period_order')->nullable()->after('period_label');
        });

        DB::table('room_prices')->select('id', 'pricing_period_id')->get()->each(function (object $row): void {
            $period = DB::table('pricing_periods')->find($row->pricing_period_id);

            if ($period) {
                DB::table('room_prices')->where('id', $row->id)->update([
                    'period_label' => $period->name,
                    'period_order' => $period->sort_order,
                ]);
            }
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('pricing_period_id');
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->string('period_label')->nullable(false)->change();
            $table->unsignedSmallInteger('period_order')->nullable(false)->change();
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->unique(['room_id', 'period_order']);
        });
    }
};
