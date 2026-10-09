<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A specific, numbered physical room belonging to a Room (the "Room
     * Type"/category, e.g. "Standard Double Room") — "Room 101", "Room
     * 102", each with its own photos and its own exact amenities, which
     * can differ slightly from its type's typical set (e.g. one Standard
     * room might have a balcony and another one in the same category
     * might not).
     */
    public function up(): void
    {
        Schema::create('room_units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->json('images')->nullable();
            $table->json('amenities')->nullable();
            $table->boolean('has_balcony')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['room_id', 'number']);
            $table->index(['room_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_units');
    }
};
