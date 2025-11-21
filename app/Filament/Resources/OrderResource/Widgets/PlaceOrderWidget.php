<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use App\Models\PlaceOrder;
use Filament\Widgets\Widget;

class PlaceOrderWidget extends Widget
{
    protected static string $view = 'filament.resources.order-resource.widgets.place-order-widget';
    public $placeOrder;
    public ?Order $record = null; // Ensure we can pass user ID when using the widget
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';

    public function mount()
    {
        $this->placeOrder = PlaceOrder::with('customer')->findOrFail($this->record->place_order_id);
    }
}
