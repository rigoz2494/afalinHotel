<?php

namespace App\Http\Resources;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
{
    /**
     * @return array{code: string, symbol: string, symbol_position: string, exchange_rate: float, is_base: bool}
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'symbol' => $this->symbol,
            'symbol_position' => $this->symbol_position->value,
            'exchange_rate' => $this->exchange_rate,
            'is_base' => $this->is_base,
        ];
    }
}
