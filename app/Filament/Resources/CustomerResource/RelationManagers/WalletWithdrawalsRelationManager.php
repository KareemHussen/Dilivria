<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WalletWithdrawalsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletWithdrawals';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Wallet Withdrawals');
    }

    public static function getLabel(): ?string
    {
        return __('Wallet Withdrawals');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label(__('Payment Method'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vodafone_cash' => 'Vodafone Cash',
                        'instapay' => 'Instapay',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vodafone_cash' => 'danger',
                        'instapay' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_number')
                    ->label(__('Payment Number/Address')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Requested At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('Pending'),
                        'completed' => __('Completed'),
                        'rejected' => __('Rejected'),
                    ])
                    ->label(__('Status')),
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        'vodafone_cash' => __('Vodafone Cash'),
                        'instapay' => __('Instapay'),
                    ])
                    ->label(__('Payment Method')),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->label(__('Accept'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->status = 'completed';
                        $record->save();
                        
                        // Update wallet balance
                        $wallet = $record->wallet;
                        $wallet->balance -= $record->amount;
                        $wallet->save();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label(__('Reason for rejection'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->status = 'rejected';
                        $record->reject_reason = $data['reject_reason'];
                        $record->save();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
