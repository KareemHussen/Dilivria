<x-filament::widget>
    <x-filament::card>

        @if ($placeOrder)
            <div class="mt-4" style="display: grid; grid-template-columns: 1fr 1fr;gap: 24px">
                <p><strong>{{ __("Customer") }}:</strong> {{ $placeOrder->customer->full_name }}</p>
                <p><strong>{{ __("From Address") }}:</strong> {{ $placeOrder->address_from }}</p>
                <p><strong>{{ __("To Address") }}:</strong> {{ $placeOrder->address_to }}</p>
                <p><strong>{{ __("Price") }}:</strong> EGP {{ number_format($placeOrder->price, 2) }}</p>
                <p><strong>{{ __("Payment Method") }}:</strong>  {{ $placeOrder->payment_method == "wallet" ? __('favorite.wallet') : __('Cash')}}</p>
                <p style="grid-column: span 2"><strong>{{ __('Details')}}:</strong> {{ $placeOrder->details }}</p>
            </div>

        @else
            <p class="text-gray-500">{{__("No order data available")}}.</p>
        @endif
    </x-filament::card>

</x-filament::widget>
