<?php

namespace App\Filament\Resources\PopularPlaceSuggestionResource\Pages;

use App\Filament\Resources\PopularPlaceSuggestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPopularPlaceSuggestion extends EditRecord
{
    protected static string $resource = PopularPlaceSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
