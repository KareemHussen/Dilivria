<?php

namespace App\Filament\Resources\MiscPagesResource\Pages;

use App\Filament\Resources\MiscPagesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMiscPages extends ViewRecord
{
    protected static string $resource = MiscPagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
