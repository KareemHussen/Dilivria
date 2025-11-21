<?php

namespace App\Filament\Resources\CustomerAllResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FavoritesRelationManager extends RelationManager
{
    protected static string $relationship = 'favorites';

    public static function getLabel(): ?string
    {
        return __('Favorite Address');  // Translation function works here
    }
    public static function getRecordTitleAttribute(): ?string
    {
        return __("Favorite Addresses");   
    }
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __("Favorite Addresses");   
    }
    public static function getPluralLabel(): ?string
    {
        return __('Favorite Addresses');  // For plural label translations
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__("Address Name"))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label(__("Address"))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label(__("Address Name")),
                Tables\Columns\TextColumn::make('address')
                ->label(__("Address")),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
