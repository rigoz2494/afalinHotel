<?php

namespace App\Filament\Resources\CallbackRequests\Pages;

use App\Filament\Resources\CallbackRequests\CallbackRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCallbackRequest extends CreateRecord
{
    protected static string $resource = CallbackRequestResource::class;
}
