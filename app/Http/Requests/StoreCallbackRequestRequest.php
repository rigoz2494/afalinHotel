<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallbackRequestRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'message' => ['nullable', 'string', 'max:2000'],
            'wants_balcony' => ['nullable', 'boolean'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            // Not validated against real inventory — a guest preference
            // noted while browsing the Section 2 modal, like wants_balcony.
            'room_number' => ['nullable', 'string', 'max:20'],
            'currency' => ['nullable', 'string', 'size:3'],
            'rooms' => ['nullable', 'array', 'max:20'],
            'rooms.*.room_id' => ['required', 'integer'],
            'rooms.*.room_name' => ['required', 'string', 'max:150'],
            'rooms.*.period' => ['nullable', 'string', 'max:100'],
            // Informational only: the server recalculates every price.
            'rooms.*.price' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
