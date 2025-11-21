<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
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
                    ->numeric()
                    ->prefix('EGP'),
                Forms\Components\TextInput::make('rate_delivery'),
                Forms\Components\TextInput::make('rate_customer'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\DateTimePicker::make('delivery_time'),
            ]);
    }
    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('Order Number'))
                    ->numeric(),
                Tables\Columns\TextColumn::make('placeOrder.customer.full_name')
                    ->label(__('Ordered By'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery.full_name')
                    ->numeric()
                    ->label(__('Delivery By')),
                Tables\Columns\TextColumn::make('price')
                    ->money("EGP")
                    ->label(__("Price"))
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
                )
                ->sortable(),
                Tables\Columns\TextColumn::make('status')
                ->label(__('Order Status'))
                ->formatStateUsing(function ($record) {

                        switch ($record->status) {
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
                Tables\Columns\TextColumn::make('delivery_time')
                    ->label(__("Delivery Time"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__("Creation Date"))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
        ->placeholder(__('All Statuses')),
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->url(fn(Order $record) => ( '/admin/orders/' . $record->id))
                    ->openUrlInNewTab(false), // Ensure it doesn't open in a new tab
                // Tables\Actions\Action::make('cancelled_user')
                //     ->label(__("Cancel Customer"))
                //     ->color('danger')
                //     ->action(fn(Order $record) => $record->update(['status' => 'cancelled_user'])),
                // Tables\Actions\Action::make('cancelled_delivery')
                //     ->label(__("Cancel Delivery"))
                //     ->color('danger')
                //     ->action(fn(Order $record) => $record->update(['status' => 'cancelled_delivery'])),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
