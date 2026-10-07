<?php

namespace App\Filament\Resources\Currencies\Pages;

use App\Filament\Resources\Currencies\CurrencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (): bool => $this->getRecord()->is_base),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
