<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Message;
use App\Models\Order;
use Filament\Widgets\Widget;

class OrderMessagesWidget extends Widget
{
    protected static string $view = 'filament.resources.order-resource.widgets.order-messages-widget';
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';

    public ?Order $record = null; // Ensure we can pass user ID when using the widget
    public ?string $orderId;

    public function mount(): void
    {
        $this->orderId = $this->record->id;
    }


    public function getMessages()
    {
        return Message::where('order_id', $this->orderId)
            ->latest()
            ->get();
    }
}
