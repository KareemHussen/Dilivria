<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\RelationManagers\ReceivedTransactionsRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\SentTransactionsRelationManager;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\WalletRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\WalletWithdrawalsRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function getLabel(): ?string
    {
        return __('Delivery');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Delivery Men');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('picture')
                    ->label(__('Picture'))
                    ->disk('public')
                    ->directory('profile')
                    ->default(null),
                Forms\Components\TextInput::make('full_name')
                    ->label(__('Full Name'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('username')
                    ->label(__('username'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone'))
                    ->required(),
                Forms\Components\TextInput::make('delivery_rate')
                    ->label(__('Delivery Rate'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('national_id')
                    ->label(__('National ID'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\FileUpload::make('id_front')
                    ->label(__('ID Front'))
                    ->disk('public')
                    ->directory('profile')
                    ->default(null),
                Forms\Components\FileUpload::make('id_back')
                    ->label(__('ID Back'))
                    ->disk('public')
                    ->directory('profile')
                    ->default(null),
                Forms\Components\FileUpload::make('selfie')
                    ->label(__('Selfie'))
                    ->disk('public')
                    ->directory('profile')
                    ->default(null),
                Forms\Components\Select::make('delivery_status')
                    ->label(__('Delivery Status'))
                    ->options([
                        'undefined' => __('Undefined'),
                        'waiting' => __('Waiting'),
                        'approved' => __('Approved'),
                        'hold' => __('Suspended'),
                        'block' => __('Blocked'),
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('delivery', 1))
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('Full Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label(__('Username'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_status')
                    ->label(__('Delivery Status')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Creation Date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn(Customer $record) => $record->update(['delivery_status' => 'approved']))
                    ->visible(fn($record) => $record->delivery_status != 'approved'),
                Tables\Actions\Action::make('block')
                    ->label(__('Block'))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->action(fn(Customer $record) => $record->update(['delivery_status' => 'block']))
                    ->visible(fn($record) => $record->delivery_status != 'block'),
                Tables\Actions\Action::make('hold')
                    ->label(__('Suspend Temporarily'))
                    ->color('warning')
                    ->icon('heroicon-o-exclamation-circle')
                    ->action(fn(Customer $record) => $record->update(['delivery_status' => 'hold']))
                    ->visible(fn($record) => $record->delivery_status != 'hold'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
            WalletRelationManager::class,
            WalletWithdrawalsRelationManager::class,
            SentTransactionsRelationManager::class,
            ReceivedTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
