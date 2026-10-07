<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An explicit, literal price for one room's one season, used when the
     * real rate doesn't fit `base_price * (1 + modifier_percentage / 100)`
     * closely enough to leave to the formula — set directly, it wins over
     * the computed one. A string, not a number, so a non-numeric display
     * value (e.g. "900/1300" for a child/adult split rate) is just as valid
     * as a plain override price.
     */
    public function up(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->string('price_override')->nullable()->after('pricing_period_id');
        });
    }

    public function down(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropColumn('price_override');
        });
    }
};
