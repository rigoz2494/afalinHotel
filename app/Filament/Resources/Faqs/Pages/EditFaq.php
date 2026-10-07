<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\Concerns\HasTranslateFaqAction;
use App\Filament\Resources\Faqs\FaqResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFaq extends EditRecord
{
    use HasTranslateFaqAction;

    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->translateFaqAction(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
