<?php

namespace App\Models;

use App\Enums\CallbackRequestStatus;
use App\Observers\CallbackRequestObserver;
use Database\Factories\CallbackRequestFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string|null $message
 * @property string|null $message_translated
 * @property bool $wants_balcony
 * @property string|null $special_requests
 * @property string|null $special_requests_translated
 * @property string|null $room_number
 * @property array<int, array{room_id: int, room_name: string, period: string|null, currency: string, base_price: float, price: float, quantity: int}> $rooms
 * @property array<int, array{service_id: int, name: string, currency: string, base_price: float, price: float, quantity: int}> $services
 * @property float $total_price
 * @property string|null $currency
 * @property float $exchange_rate
 * @property CallbackRequestStatus $status
 * @property string|null $ip_address
 */
#[ObservedBy([CallbackRequestObserver::class])]
class CallbackRequest extends Model
{
    /** @use HasFactory<CallbackRequestFactory> */
    use HasFactory;

    protected $fillable = ['name', 'phone', 'message', 'message_translated', 'wants_balcony', 'special_requests', 'special_requests_translated', 'room_number', 'rooms', 'services', 'total_price', 'currency', 'exchange_rate', 'status', 'ip_address'];

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
