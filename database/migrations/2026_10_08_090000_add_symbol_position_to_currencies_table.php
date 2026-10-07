<?php

use App\Enums\CurrencySymbolPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table): void {
            $table->string('symbol_position')->default(CurrencySymbolPosition::Before->value)->after('symbol');
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table): void {
            $table->dropColumn('symbol_position');
        });
    }
};
