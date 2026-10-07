<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the manual per-room, per-month price grid: every room's rate
     * for this period is now computed as `base_price * (1 + modifier / 100)`,
     * so a single admin-set value prices every room for that season at once.
     */
    public function up(): void
    {
        Schema::table('pricing_periods', function (Blueprint $table): void {
            $table->decimal('modifier_percentage', 6, 2)->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_periods', function (Blueprint $table): void {
            $table->dropColumn('modifier_percentage');
        });
    }
};
