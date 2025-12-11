<?php

namespace App\Filament\Resources\PopularPlaceSuggestionResource\Pages;

use App\Filament\Resources\PopularPlaceSuggestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPopularPlaceSuggestion extends ViewRecord
{
    protected static string $resource = PopularPlaceSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
