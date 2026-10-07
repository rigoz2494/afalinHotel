<?php

namespace App\Models;

use App\Enums\CurrencySymbolPosition;
use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * @property int $id
 * @property string $code
 * @property string $symbol
 * @property float $exchange_rate
 * @property CurrencySymbolPosition $symbol_position
 * @property bool $is_base
 * @property bool $is_active
 * @property int $sort_order
 */
#[Fillable(['code', 'symbol', 'symbol_position', 'exchange_rate', 'is_base', 'is_active', 'sort_order'])]
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exchange_rate' => 'float',
            'symbol_position' => CurrencySymbolPosition::class,
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Currency $currency): void {
            $currency->code = strtoupper($currency->code);

            if ($currency->is_base) {
                // The base currency is the reference point, so its rate is always 1
                // and it can never be inactive. Only one currency can be the base.
                $currency->exchange_rate = 1;
                $currency->is_active = true;

                static::query()
                    ->where('is_base', true)
                    ->when($currency->exists, fn (Builder $query) => $query->whereKeyNot($currency->getKey()))
                    ->update(['is_base' => false]);
            } elseif ($currency->exists && $currency->getOriginal('is_base')) {
                throw new RuntimeException('Choose another base currency before unmarking this one.');
            }
        });

        static::deleting(function (Currency $currency): void {
            if ($currency->is_base) {
                throw new RuntimeException('The base currency cannot be deleted.');
            }
        });
    }

    /**
     * @param  Builder<Currency>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Currency>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public static function base(): ?self
    {
        return static::query()->where('is_base', true)->first();
    }

    /**
     * The symbol the admin shows next to prices. Prices are stored in the base
     * currency, so this is the same currency the frontend converts from.
     */
    public static function baseSymbol(): string
    {
        return static::base()?->symbol ?? '$';
    }

    /**
     * The symbol for a specific currency code, such as a booking's locked currency.
     */
    public static function symbolFor(?string $code): string
    {
        return ($code === null ? null : static::query()->where('code', $code)->value('symbol'))
            ?? static::baseSymbol();
    }
}
