<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The automatic translation previously only covered special_requests;
     * the main guest message needs the same treatment so the admin's
     * unified translation block can show both.
     */
    public function up(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->text('message_translated')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->dropColumn('message_translated');
        });
    }
};
