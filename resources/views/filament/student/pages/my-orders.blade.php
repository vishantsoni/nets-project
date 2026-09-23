<x-filament-panels::page>
    @php
        $orders = \App\Models\Order::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
    @endphp

    @if($orders->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b">
                        <th class="pb-3">Order #</th>
                        <th class="pb-3">Date</th>
                        <th class="pb-3">Total</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Payment</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr class="border-b">
                            <td class="py-3">{{ $order->order_number }}</td>
                            <td class="py-3">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="py-3">Rs. {{ $order->total_amount }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'processing') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                    @elseif($order->payment_status === 'failed') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="py-3">
                                <a href="{{ route('student.orders.show', $order->id) }}" class="text-sm text-primary-600 hover:underline">Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">No orders yet.</p>
        <a href="{{ url('/store') }}" class="text-sm text-primary-600 hover:underline">Browse Materials</a>
    @endif
</x-filament-panels::page>
