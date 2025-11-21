<x-filament::widget>
    <x-filament::card>
        @php
            $messages = $this->getMessages();
        @endphp
        <h2 class="text-lg font-bold text center" style="text-align: center; padding: 16px">{{__("Messages Between Customer and Delivery")}}</h2>
        @if ($messages->count())
            <ul style="max-height: 400px;overflow: auto;" id="chat">
                @foreach ($messages as $message)
                @if ($message->sender_type == 'customer')

                    <li class="mb-4" style="min-width: 200px;margin-left: {{$message->sender_type == 'customer' ? 'auto' : 0}};margin-right: {{$message->sender_type == 'delivery' ? 'auto' : 0}};width: max-content; padding: 0 16px">
                        <strong>{{ $message->sender_type == "delivery" ? __("Delivery") : __("Customer") }}:</strong>
                        <p style="padding: 8px 16px;background: #6868ff;margin: 4px;border-radius: 34px;">{{ $message->message }}</p>
                        <span class="text-gray-500 text-sm">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                    </li>
                @endif
                @if ($message->sender_type == 'delivery')

                    <li class="mb-4" style="min-width: 200px;margin-left: {{$message->sender_type == 'customer' ? 'auto' : 0}};margin-right: {{$message->sender_type == 'delivery' ? 'auto' : 0}};width: max-content; padding: 0 16px">
                        <strong>{{ $message->sender_type == "delivery" ? __("Delivery") : __("Customer") }}:</strong>
                        <p style="padding: 8px 16px;background: #5cc059;margin: 4px;border-radius: 34px;">{{ $message->message }}</p>
                        <span class="text-gray-500 text-sm">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                    </li>
                @endif
                @endforeach
            </ul>
        @else
            <p class="text-gray-500" style="text-align: center;padding: 24px 0 0px;">{{__("No messages available")}}.</p>
        @endif
    </x-filament::card>

    <script>
        setTimeout(() => {
            const messageList = document.getElementById('chat');
            if (messageList) {
                messageList.scrollTop = messageList.scrollHeight;
            }
        }, 1000);
    </script>
</x-filament::widget>
