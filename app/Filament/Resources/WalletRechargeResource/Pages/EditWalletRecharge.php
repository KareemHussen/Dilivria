<?php

namespace App\Filament\Resources\WalletRechargeResource\Pages;

use App\Filament\Resources\WalletRechargeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;

class EditWalletRecharge extends EditRecord
{
    protected static string $resource = WalletRechargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('accept')
                    ->label(__('Accept'))
                    ->color('success')
                    ->modalHeading(__('Accept Recharge'))
                    ->modalSubheading(__('Enter the amount to add to the wallet'))
                    ->modalButton(__('Add Amount'))
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->prefix(__("EGP"))
                            ->required()
                            ->numeric()
                            ->label(__('Amount')),
                    ])
                    ->action(function ($record, array $data) {
                        $wallet = $record->wallet;
                        $wallet->balance += $data['amount'];
                        $wallet->save();

                        $record->status = 'accepted';
                        $record->save();
                    })
                    ->visible(
                        fn($record) => $record->status === 'pending'
                    ),

                    Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->color('danger')
                    ->modalHeading(__('Reject Recharge'))
                    ->modalSubheading(__('Provide a reason for rejecting this recharge'))
                    ->modalButton(__('Submit'))
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->required()
                            ->label(__('Rejection Reason')),
                    ])
                    ->action(function ($record, array $data) {
                        $record->status = 'rejected';
                        $record->reject_reason = $data['reject_reason'];
                        $record->save();
                    })
                    ->visible(
                        fn($record) => $record->status === 'pending'
                    ),
        ];
    }
}
