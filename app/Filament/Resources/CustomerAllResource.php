<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerAllResource\Pages;
use App\Filament\Resources\CustomerAllResource\RelationManagers\FavoritesRelationManager;
use App\Filament\Resources\CustomerAllResource\RelationManagers\PlaceOrdersRelationManager;
use App\Filament\Resources\CustomerAllResource\RelationManagers\ReceivedTransactionsRelationManager;
use App\Filament\Resources\CustomerAllResource\RelationManagers\SentTransactionsRelationManager;
use App\Filament\Resources\CustomerAllResource\RelationManagers\WalletRelationManager;
use App\Filament\Resources\CustomerAllResource\RelationManagers\WalletWithdrawalsRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerAllResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getLabel(): ?string
    {
        return __('Customer');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Customers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->label(__('First Name'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('last_name')
                    ->label(__('Last Name'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('username')
                    ->label(__('Username'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone'))
                    ->required(),
                Forms\Components\TextInput::make('full_name')
                    ->label(__('Full Name'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('customer_rate')
                    ->label(__('Customer Rate'))
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('Name'))
                    ->formatStateUsing(function ($record) {
                        return $record->first_name . ' ' . $record->last_name;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $query) use ($search): Builder {
                            return $query->where('first_name', 'like', "%{$search}%")
                                         ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('username')
                    ->label(__('Username'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('Full Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_rate')
                    ->label(__('Customer Rate'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PlaceOrdersRelationManager::class,
            WalletRelationManager::class,
            WalletWithdrawalsRelationManager::class,
            FavoritesRelationManager::class,
            SentTransactionsRelationManager::class,
            ReceivedTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerAlls::route('/'),
            'create' => Pages\CreateCustomerAll::route('/create'),
            'view' => Pages\ViewCustomerAll::route('/{record}'),
        ];
    }
}
