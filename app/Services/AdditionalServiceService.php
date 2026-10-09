<?php

namespace App\Services;

use App\Models\AdditionalService;
use Illuminate\Database\Eloquent\Collection;

class AdditionalServiceService
{
    /**
     * @return Collection<int, AdditionalService>
     */
    public function listActive(): Collection
    {
        return AdditionalService::query()->active()->ordered()->get();
    }
}
