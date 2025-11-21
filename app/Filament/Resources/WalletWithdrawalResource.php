<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletWithdrawalResource\Pages;
use App\Models\WalletWithdrawal;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WalletWithdrawalResource extends Resource
{
    protected static ?string $model = WalletWithdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Wallet Management';

    public static function getLabel(): ?string
    {
        return __('Wallet Withdrawal');
    }

    public static function getPluralLabel(): ?string
    {
        return __("Wallet Withdrawals");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->label(__('Amount'))
                    ->prefix('EGP')
                    ->required(fn ($context) => $context === 'create')
                    ->numeric()
                    ->minValue(1)
                    ->disabled(fn ($context) => $context === 'edit'),
                    
                Forms\Components\Select::make('payment_type')
                    ->label(__('Payment Method'))
                    ->options([
                        'vodafone_cash' => 'Vodafone Cash',
                        'instapay' => 'Instapay',
                    ])
                    ->required()
                    ->reactive()
                    ->disabled(fn ($context) => $context === 'edit'),
                    
                TextInput::make('payment_number')
                    ->label(fn ($get) => $get('payment_type') === 'vodafone_cash' ? __('Phone Number') : __('Instapay Address'))
                    ->placeholder(fn ($get) => $get('payment_type') === 'vodafone_cash' ? '01012345678' : '@username')
                    ->helperText(fn ($get) => $get('payment_type') === 'vodafone_cash' ? 
                        __('wallet.vodafone_number_help') : 
                        __('wallet.instapay_address_help'))
                    ->required()
                    ->disabled(fn ($context) => $context === 'edit')
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $paymentType = $get('payment_type');
                                
                                if ($paymentType === 'vodafone_cash') {
                                    if (!preg_match('/^01[0-2,5]{1}[0-9]{8}$/', $value)) {
                                        $fail(__('wallet.invalid_vodafone_number'));
                                    }
                                } elseif ($paymentType === 'instapay') {
                                    if (!preg_match('/^@[a-zA-Z0-9_]{3,}$/', $value)) {
                                        $fail(__('wallet.invalid_instapay_address'));
                                    }
                                }
                            };
                        },
                    ]),
                TextInput::make('status')
                    ->label(__('Status'))
                    ->disabled(),
                Forms\Components\Textarea::make('reject_reason')
                    ->label(__('Rejection Reason'))
                    ->disabled()
                    ->visible(fn ($record) => $record->status === 'rejected')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wallet.customer.username')
                    ->label(__('Username'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('wallet.customer.phone')
                    ->label(__('Phone'))
                    ->searchable(),
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
                    ->formatStateUsing(function ($record) {
                        return match ($record->status) {
                            'pending' => __('Pending'),
                            'completed' => __('Completed'),
                            'rejected' => __('Rejected'),
                            default => $record->status
                        };
                    })
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
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('Complete'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->modalHeading(__('Complete Withdrawal'))
                    ->modalSubheading(__('Are you sure you want to mark this withdrawal as completed?'))
                    ->modalButton(__('Complete Withdrawal'))
                    ->action(function ($record) {
                        $record->status = 'completed';
                        $record->save();
                    })
                    ->visible(fn($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->modalHeading(__('Reject Withdrawal'))
                    ->modalSubheading(__('Provide a reason for rejecting this withdrawal request'))
                    ->modalButton(__('Reject'))
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->required()
                            ->label(__('Rejection Reason')),
                    ])
                    ->action(function ($record, array $data) {
                        // Return the amount to the user's wallet
                        $wallet = $record->wallet;
                        $wallet->balance += $record->amount;
                        $wallet->save();

                        $record->status = 'rejected';
                        $record->reject_reason = $data['reject_reason'];
                        $record->save();
                    })
                    ->visible(fn($record) => $record->status === 'pending'),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletWithdrawals::route('/'),
            'view' => Pages\ViewWalletWithdrawal::route('/{record}'),
        ];
    }
}
