<?php

namespace App\Filament\Resources\MiscPagesResource\Pages;

use App\Filament\Resources\MiscPagesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMiscPages extends EditRecord
{
    protected static string $resource = MiscPagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
