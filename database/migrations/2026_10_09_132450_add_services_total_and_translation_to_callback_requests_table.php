<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `services` mirrors `rooms`: a server-priced snapshot of whichever
     * AdditionalService upsells the guest picked, never trusted from the
     * client. `total_price` is the one authoritative sum of both — stored
     * rather than left for the frontend or admin to re-add up by hand, so
     * there's a single number nothing else can quietly disagree with.
     * `special_requests_translated` holds the automatic Russian translation
     * of `special_requests`, filled in by CallbackRequestObserver.
     */
    public function up(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->json('services')->nullable()->after('rooms');
            $table->decimal('total_price', 10, 2)->nullable()->after('services');
            $table->text('special_requests_translated')->nullable()->after('special_requests');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->dropColumn(['services', 'total_price', 'special_requests_translated']);
        });
    }
};
