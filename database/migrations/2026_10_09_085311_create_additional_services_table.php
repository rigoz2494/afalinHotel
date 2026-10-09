<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standalone upsells with a flat price each (Parking, Breakfast, an
     * extra bed for a child or an adult, ...) — shown as a reference list
     * below the pricing matrix, not as rows within it, since they aren't
     * priced per season the way a room is.
     */
    public function up(): void
    {
        Schema::create('additional_services', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_services');
    }
};
