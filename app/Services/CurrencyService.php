<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    /**
     * The currencies a guest may choose from, base currency first.
     *
     * @return Collection<int, Currency>
     */
    public function selectable(): Collection
    {
        return Currency::query()->active()->ordered()->get()
            ->sortByDesc('is_base')
            ->values();
    }
}
