<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            // Free text ("quiet side", "separate blankets", ...), and the
            // specific numbered room the guest browsed in the Section 2
            // modal, if any — both are guest preferences, not validated
            // against real inventory, the same way wants_balcony is.
            $table->text('special_requests')->nullable()->after('wants_balcony');
            $table->string('room_number')->nullable()->after('special_requests');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->dropColumn(['special_requests', 'room_number']);
        });
    }
};
