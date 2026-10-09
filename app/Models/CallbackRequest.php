<?php

namespace App\Models;

use App\Enums\CallbackRequestStatus;
use App\Observers\CallbackRequestObserver;
use Database\Factories\CallbackRequestFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([CallbackRequestObserver::class])]
class CallbackRequest extends Model
{
    /** @use HasFactory<CallbackRequestFactory> */
    use HasFactory;

    protected $fillable = ['name', 'phone', 'message', 'wants_balcony', 'special_requests', 'special_requests_translated', 'room_number', 'rooms', 'services', 'total_price', 'currency', 'exchange_rate', 'status', 'ip_address'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CallbackRequestStatus::class,
            'wants_balcony' => 'boolean',
            'rooms' => 'array',
            'services' => 'array',
            'total_price' => 'float',
            'exchange_rate' => 'float',
        ];
    }
}
