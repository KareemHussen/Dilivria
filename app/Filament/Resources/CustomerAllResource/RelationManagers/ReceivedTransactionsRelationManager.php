<?php

namespace App\Filament\Resources\CustomerAllResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class ReceivedTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'receivedTransactions';
    public static function getLabel(): ?string
    {
        return __('Received Transactions');  // Translation function works here
    }
    public static function getRecordTitleAttribute(): ?string
    {
        return __("Received Transactions");
    }
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __("Received Transactions");
    }
    public static function getPluralLabel(): ?string
    {
        return __("Received Transactions");
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->required()
                    ->money('EGP')
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')->label(__('Type')),
                Forms\Components\TextInput::make('status')->label(__('Status')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('senderWallet.customer.username')->label(__('Sender')),
                Tables\Columns\TextColumn::make('amount')->money('EGP')->label(__('Amount')),
                Tables\Columns\TextColumn::make('type')
                ->formatStateUsing(
                    fn ($state) => match ($state) {
                        'pay' => __('Pay'),
                        'transfer' => __('Transfer'),
                        default => __('Undifined'),
                    }
                )
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pay' => 'primary',
                    'transfer' => 'warning',
                })
                ->label(__('Type')),
                Tables\Columns\TextColumn::make('status')
                ->formatStateUsing(
                    fn ($state) => match ($state) {
                        'pending' => __('Pending'),
                        'completed' => __('Completed'),
                        'failed' => __('Failed'),
                        default => __('Undifined'),
                    }
                )
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'info',
                    'completed' => 'success',
                    'failed' => 'danger',
                })
                ->label(__('Status')),
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
