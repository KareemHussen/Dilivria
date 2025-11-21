<?php

namespace App\Filament\Resources\CustomerAllResource\Pages;

use App\Filament\Resources\CustomerAllResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerAll extends EditRecord
{
    protected static string $resource = CustomerAllResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
