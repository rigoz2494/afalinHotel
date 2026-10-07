<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->string('bed_type');
            $table->boolean('has_tv')->default(true);
            $table->boolean('has_air_conditioning')->default(true);
            $table->json('furniture');
            $table->string('image_path')->nullable();
            $table->unsignedInteger('base_price');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
