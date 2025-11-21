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

class WalletRelationManager extends RelationManager
{
    protected static string $relationship = 'wallet';

    public static function getLabel(): ?string
    {
        return __('favorite.wallet');
    }
    public static function getRecordTitleAttribute(): ?string
    {
        return __('favorite.wallet');
    }
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('favorite.wallet');
    }
    public static function getPluralLabel(): ?string
    {
        return __('favorite.wallet');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('balance')
                    ->label(__('Balance'))
                    ->required()
                    ->prefix(__('EGP'))
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('balance')
            ->columns([
                Tables\Columns\TextColumn::make('balance')
                ->label(__('Balance'))
                ->money('EGP'),
            ])
            ->paginated(false)
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
