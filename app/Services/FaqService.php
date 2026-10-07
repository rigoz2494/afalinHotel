<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    /**
     * @return Collection<int, Faq>
     */
    public function listActive(): Collection
    {
        return Faq::query()->active()->ordered()->get();
    }
}
