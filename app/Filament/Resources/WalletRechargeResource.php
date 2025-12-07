<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletRechargeResource\Pages;
use App\Filament\Resources\WalletRechargeResource\RelationManagers;
use App\Models\WalletRecharge;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;

class WalletRechargeResource extends Resource
{
    protected static ?string $model = WalletRecharge::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationGroup = 'Wallet Management';

    public static function getLabel(): ?string
    {
        return __('Wallet Recharge');  // Translation function works here
    }
    public static function getPluralLabel(): ?string
    {
        return __("Wallet Recharges");
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('photo')
                ->label(__('Receipt'))
                ->disabled(fn ($context) => $context === 'edit')
                ->required(fn ($context) => $context === 'create'),
                
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
                ->disabled()
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                ->label(__('Receipt'))
                ->url(fn($record) => $record->photo_url), // Make image clickable using the accessor
                Tables\Columns\TextColumn::make('wallet.customer.username')
                    ->label(__('Username')),
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
                ->label(__("Status"))
                ->formatStateUsing(function ($record){
                    return $record->status == "pending" ? __("Pending") : $record->status;
                }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('Pending'),
                        'accepted' => __('Accepted'),
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
            ->actions([
                Tables\Actions\Action::make('accept')
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

                    Tables\Actions\Action::make('reject')
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
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListWalletRecharges::route('/'),
            'create' => Pages\CreateWalletRecharge::route('/create'),
            'edit' => Pages\EditWalletRecharge::route('/{record}/edit'),
        ];
    }
}
