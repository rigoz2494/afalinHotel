<?php

namespace App\Filament\Resources\CallbackRequests;

use App\Enums\CallbackRequestStatus;
use App\Filament\Resources\CallbackRequests\Pages\CreateCallbackRequest;
use App\Filament\Resources\CallbackRequests\Pages\EditCallbackRequest;
use App\Filament\Resources\CallbackRequests\Pages\ListCallbackRequests;
use App\Filament\Resources\CallbackRequests\Schemas\CallbackRequestForm;
use App\Filament\Resources\CallbackRequests\Tables\CallbackRequestsTable;
use App\Models\CallbackRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CallbackRequestResource extends Resource
{
    protected static ?string $model = CallbackRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('booking');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bookings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bookings');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Leads');
    }

    public static function form(Schema $schema): Schema
    {
        return CallbackRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallbackRequestsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', CallbackRequestStatus::New)->count();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCallbackRequests::route('/'),
            'create' => CreateCallbackRequest::route('/create'),
            'edit' => EditCallbackRequest::route('/{record}/edit'),
        ];
    }
}
