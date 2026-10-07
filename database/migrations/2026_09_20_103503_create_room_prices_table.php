<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('period_label');
            $table->unsignedSmallInteger('period_order');
            $table->unsignedInteger('price');
            $table->timestamps();

            $table->unique(['room_id', 'period_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_prices');
    }
};
