<?php

namespace App\Filament\Resources\PopularPlaceSuggestionResource\Pages;

use App\Filament\Resources\PopularPlaceSuggestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPopularPlaceSuggestions extends ListRecords
{
    protected static string $resource = PopularPlaceSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
