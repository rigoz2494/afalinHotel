<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->boolean('wants_balcony')->default(false)->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', function (Blueprint $table): void {
            $table->dropColumn('wants_balcony');
        });
    }
};
