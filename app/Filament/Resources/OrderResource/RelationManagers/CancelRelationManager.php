<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CancelRelationManager extends RelationManager
{
    protected static string $relationship = 'cancel';

    public static function getLabel(): ?string
    {
        return __('Cancel');  // Translation function works here
    }
    public static function getRecordTitleAttribute(): ?string
    {
        return __("Cancel");
    }
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __("Cancel");
    }
    public static function getPluralLabel(): ?string
    {
        return __("Cancel");
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reason')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('requested_by.full_name')
                ->label(__('Requested By')),
                Tables\Columns\TextColumn::make('requested_by_type')
                    ->label(__("Cancellation Request"))
                    ->formatStateUsing(function ($record){
                        return $record->requested_by_type == "delivery" ? __("Delivery") : __("Customer");
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label(__("Reason")),
                Tables\Columns\TextColumn::make('status')
                    ->label(__("Status"))
                    ->formatStateUsing(function ($record){
                        return $record->status == "pending" ? __("Pending") : ($record->status == "accepted" ? __("Accepted") : __("Rejected"));
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
