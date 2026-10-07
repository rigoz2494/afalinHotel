<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\Concerns\HasTranslateFaqAction;
use App\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    use HasTranslateFaqAction;

    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->translateFaqAction(),
        ];
    }
}
