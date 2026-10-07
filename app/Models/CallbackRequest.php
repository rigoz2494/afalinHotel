<?php

namespace App\Models;

use App\Enums\CallbackRequestStatus;
use Database\Factories\CallbackRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    /** @use HasFactory<CallbackRequestFactory> */
    use HasFactory;

    protected $fillable = ['name', 'phone', 'message', 'rooms', 'currency', 'exchange_rate', 'status', 'ip_address'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CallbackRequestStatus::class,
            'rooms' => 'array',
            'exchange_rate' => 'float',
        ];
    }
}
