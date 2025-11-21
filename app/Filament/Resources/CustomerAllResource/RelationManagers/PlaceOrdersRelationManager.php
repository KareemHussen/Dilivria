<?php

namespace App\Filament\Resources\CustomerAllResource\RelationManagers;

use App\Models\Order;
use App\Models\PlaceOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlaceOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'placeOrders';

    public static function getLabel(): ?string
    {
        return __('Order');  // Translation function works here
    }
    public static function getRecordTitleAttribute(): ?string
    {
        return __("Orders");
    }
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __("Orders");
    }
    public static function getPluralLabel(): ?string
    {
        return __("Orders");
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_from')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_to')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('details')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('order'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label(__('Order Number'))
                    ->numeric(),
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label(__('Ordered By'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.delivery.full_name')
                    ->searchable()
                    ->label(__('Delivery By')),
                Tables\Columns\TextColumn::make('order.price')
                    ->money("EGP")
                    ->label(__("Price")),
                // Tables\Columns\TextColumn::make('rate_delivery')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('rate_customer')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__("Payment Method"))
                    ->formatStateUsing(function ($record){
                        return $record->payment_method == "wallet" ? __("favorite.wallet") : __("Cash");
                    }),
                Tables\Columns\TextColumn::make('paid')
                    ->label(__("Pay Status"))
                    ->badge()
                    ->color(fn ( $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(
                        fn ( $record): string => $record->paid ? __('Paid') : __('Not Paid')
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.status')
                    ->label((__('Order Status')))
                    ->formatStateUsing(function ($record) {

                        switch ($record->order->status) {
                            case 'waiting':
                                return __('Waiting');
                            case 'first_point':
                                return __('First point');
                            case 'received':
                                return __('Received');
                            case 'sec_point':
                                return __('Second point');
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
                Tables\Columns\TextColumn::make('order.delivery_time')
                    ->label(__("Delivery Time"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.created_at')
                    ->label(__("Creation Date"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                ->label(__('Payment Method'))
                ->options([
                    'cash' => __('Cash'),
                    'wallet' => __('favorite.wallet'),
                ])
                ->placeholder(__('Payment Methods')),
    
            // Filter for Paid Status
            Tables\Filters\TernaryFilter::make('paid')
                ->label(__('Paid')),
    
            // Filter for Order Status
            SelectFilter::make('order.status')
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
                ->placeholder(__('All Statuses')),
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->url(fn(PlaceOrder $record) => ( '/admin/orders/' . $record->order->id))
                    ->openUrlInNewTab(false), // Ensure it doesn't open in a new tab
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
