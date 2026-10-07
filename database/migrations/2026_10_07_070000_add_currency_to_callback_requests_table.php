<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each booking locks the currency and exchange rate the guest selected, so
     * the stored prices stay unambiguous even if rates change later. Existing
     * bookings were all recorded in the base currency, hence the USD default.
     */
    public function up(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->string('currency', 3)->default('USD')->after('rooms');
            $table->decimal('exchange_rate', 12, 4)->default(1)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
