<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_prices', function (Blueprint $table): void {
            $table->unsignedTinyInteger('discount_percentage')->nullable()->after('price');
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->unsignedTinyInteger('discount_percentage')->nullable()->after('base_price');
        });

        Schema::table('room_prices', function (Blueprint $table): void {
            $table->dropColumn('discount_percentage');
        });
    }
};
