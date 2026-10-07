<?php

use App\Enums\CallbackRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('callback_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone', 32);
            $table->text('message')->nullable();
            $table->string('status')->default(CallbackRequestStatus::New->value);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_requests');
    }
};
