<?php

namespace App\Models;

use Database\Factories\AdditionalServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A standalone upsell with a flat price (Parking, Breakfast, an extra bed
 * for a child or an adult, ...), shown as a reference list below the
 * pricing matrix rather than as a row within it.
 */
class AdditionalService extends Model
{
    /** @use HasFactory<AdditionalServiceFactory> */
    use HasFactory;

    protected $fillable = ['name', 'price', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'price' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<AdditionalService>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<AdditionalService>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
