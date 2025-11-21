<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\CancelRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function getLabel(): ?string
    {
        return __('order.order');  // Translation function works here
    }
    public static function getPluralLabel(): ?string
    {
        return __("Orders");
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['placeOrder.customer', 'delivery']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('delivery.full_name')
                ->label(__('Delivery By')),
                Forms\Components\TextInput::make('delivery.username')
                ->label(__('Username')),
                Forms\Components\TextInput::make('price')
                    ->label(__("Price"))
                    ->required()
                    ->numeric()
                    ->prefix('EGP'),
                Forms\Components\TextInput::make('rate_delivery')
                    ->label(__("Delivery Rating")),
                Forms\Components\TextInput::make('rate_customer')
                    ->label(__("Customer Rating")),
                Forms\Components\TextInput::make('status')
                    ->label(__("Order Status"))
                    ->required(),
                Forms\Components\DateTimePicker::make('delivery_time')
                    ->label(__("Delivery Time")),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label(__('Order Number'))
                ->numeric(),
                Tables\Columns\TextColumn::make('placeOrder.customer.full_name')
                    ->label(__('Ordered By'))
                    ->numeric(),
                Tables\Columns\TextColumn::make('delivery.full_name')
                    ->numeric()
                    ->label(__('Delivery By')),
                Tables\Columns\TextColumn::make('price')
                    ->label(__("Price"))
                    ->money("EGP")
                    ->sortable(),
                // Tables\Columns\TextColumn::make('rate_delivery')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('rate_customer')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('placeOrder.payment_method')
                    ->label(__("Payment Method"))
                    ->formatStateUsing(function ($record){
                        return $record->placeOrder->payment_method == "wallet" ? __("favorite.wallet") : __("Cash");
                    }),
                Tables\Columns\TextColumn::make('placeOrder.paid')
                    ->label(__("Pay Status"))
                    ->badge()
                    ->color(fn ( $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(
                        fn ( $record): string => $record->placeOrder->paid ? __('Paid') : __('Not Paid')
                        ),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Order Status'))
                    ->formatStateUsing(function ($record) {

                        switch ($record->status) {
                            case 'waiting':
                                return __('Waiting');
                            case 'first_point':
                                return __('First Point');
                            case 'received':
                                return __('Received');
                            case 'sec_point':
                                return __('Second Point');
                            case 'completed':
                                return __('Completed');
                            case 'cancelled_user':
                                return __('Cancelled by user');
                            case 'cancelled_delivery':
                                return __('Cancelled by delivery');
                            default:
                                return __('Unknown Status');
                    }
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_time')
                    ->label(__("Delivery Time"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__("Creation Date"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                ->label(__('Payment Method'))
                ->options(function () {
                    return [
                        'wallet' => __('favorite.wallet'),
                        'cash' => __('Cash')
                    ];
                })
                ->query(function (Builder $query, array $data) {
                    if ($data['value']) {
                        return $query->whereHas('placeOrder', function ($q) use ($data) {
                            $q->where('payment_method', $data['value']);
                        });
                    }
                    return $query;
                }),
                // Filter for Pay Status (placeOrder.paid)
                Tables\Filters\TernaryFilter::make('placeOrder.paid')
                    ->label(__('Pay Status'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('placeOrder', function ($q){
                            $q->where('paid', 1);
                        }),
                        false: fn (Builder $query) => $query->whereHas('placeOrder', function ($q){
                            $q->where('paid', 0);
                        }),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    ),
                // Filter for Order Status (status)
                SelectFilter::make('status')
                    ->label(__('Order Status'))
                    ->options([
                        'waiting' => __('Waiting'),
                        'first_point' => __('First Point'),
                        'received' => __('Received'),
                        'sec_point' => __('Second Point'),
                        'completed' => __('Completed'),
                        'cancelled_user' => __('Cancelled by user'),
                        'cancelled_delivery' => __('Cancelled by delivery'),
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancelled_user')
                ->label('إيقاف كمستخدم')
                ->color('danger')
                ->action(fn(Order $record) => $record->update(['status' => 'cancelled_user'])),
                Tables\Actions\Action::make('cancelled_delivery')
                ->label('إيقاف كمندوب')
                ->color('danger')
                ->action(fn(Order $record) => $record->update(['status' => 'cancelled_delivery'])),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CancelRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
