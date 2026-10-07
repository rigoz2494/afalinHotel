<?php

namespace App\Filament\Resources\Currencies\Pages;

use App\Exceptions\ExchangeRateSyncException;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Services\ExchangeRateSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCurrencies extends ListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncExchangeRates')
                ->label(__('Update Exchange Rates'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(__('Fetches live rates and updates every other active currency relative to the current base currency. This only runs when you click it.'))
                // Manual only: no cron job or queue listener ever calls this.
                ->action(function (ExchangeRateSyncService $exchangeRates): void {
                    try {
                        $result = $exchangeRates->syncFromBase();

                        Notification::make()
                            ->title(__('Exchange rates updated'))
                            ->body(trans_choice(
                                '{0} No other active currency needed updating.|{1} :count currency was updated relative to :base.|[2,*] :count currencies were updated relative to :base.',
                                $result['updated'],
                                ['count' => $result['updated'], 'base' => $result['base']],
                            ))
                            ->success()
                            ->send();
                    } catch (ExchangeRateSyncException $exception) {
                        Notification::make()
                            ->title(__('Exchange rate update failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
