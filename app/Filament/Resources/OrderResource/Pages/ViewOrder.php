<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderMessagesWidget;
use App\Filament\Resources\OrderResource\Widgets\PlaceOrderWidget;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancelled_user')
            ->label('إيقاف كمستخدم')
            ->color('danger')
            ->action(fn(Order $record) => $record->update(['status' => 'cancelled_user'])),
            Actions\Action::make('cancelled_delivery')
            ->label('إيقاف كمندوب')
            ->color('danger')
            ->action(fn(Order $record) => $record->update(['status' => 'cancelled_delivery'])),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            PlaceOrderWidget::make([
                'record' => $this->record,
            ]),
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            OrderMessagesWidget::make([
                'record' => $this->record,
            ]),
        ];
    }
}
