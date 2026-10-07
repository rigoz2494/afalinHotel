<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The season's `modifier_percentage` now computes every room's regular
     * rate for that period, so an admin-entered absolute price per room is no
     * longer needed. `room_prices` keeps only the optional, room-specific
     * promotional discount it already stored alongside that price.
     */
    public function up(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropColumn('price');
        });
    }

    public function down(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->unsignedInteger('price')->default(0)->after('pricing_period_id');
        });
    }
};
