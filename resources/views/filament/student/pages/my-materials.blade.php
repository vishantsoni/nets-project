<x-filament-panels::page>
    <h3 class="text-lg font-semibold mb-4">My Study Materials</h3>

    @php
        $freeMaterials = \App\Models\StudyMaterial::where('is_free', true)->where('is_published', true)->with('subject')->orderBy('created_at', 'desc')->get();
        $purchasedMaterials = \App\Models\StudyMaterial::where('is_paid', true)->where('is_published', true)
            ->whereHas('orderItems', function ($q) {
                $q->where('orders.user_id', auth()->id())->where('orders.payment_status', 'paid');
            })->with('subject')->orderBy('created_at', 'desc')->get();
        $paidMaterials = \App\Models\StudyMaterial::where('is_paid', true)->where('is_published', true)
            ->whereDoesntHave('orderItems', function ($q) {
                $q->where('orders.user_id', auth()->id())->where('orders.payment_status', 'paid');
            })->with('subject')->orderBy('created_at', 'desc')->get();
    @endphp

    <div class="mb-6">
        <h4 class="text-md font-medium mb-3">Free Materials</h4>
        @if($freeMaterials->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($freeMaterials as $material)
                    <div class="p-4 border rounded-lg">
                        <h5 class="font-medium">{{ $material->title }}</h5>
                        <p class="text-sm text-gray-600">{{ $material->subject->name ?? '' }}</p>
                        <div class="mt-2">
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Free</span>
                        </div>
                        <a href="{{ Storage::url($material->file_path) }}" class="text-sm text-primary-600 hover:underline mt-2 block">Download</a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No free materials available.</p>
        @endif
    </div>

    <div class="mb-6">
        <h4 class="text-md font-medium mb-3">My Purchased Materials</h4>
        @if($purchasedMaterials->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($purchasedMaterials as $material)
                    <div class="p-4 border rounded-lg">
                        <h5 class="font-medium">{{ $material->title }}</h5>
                        <p class="text-sm text-gray-600">{{ $material->subject->name ?? '' }}</p>
                        <a href="{{ Storage::url($material->file_path) }}" class="text-sm text-primary-600 hover:underline mt-2 block">Download</a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No purchased materials yet.</p>
            <a href="{{ url('/store') }}" class="text-sm text-primary-600 hover:underline">Browse Materials</a>
        @endif
    </div>

    <div>
        <h4 class="text-md font-medium mb-3">Available for Purchase</h4>
        @if($paidMaterials->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($paidMaterials as $material)
                    <div class="p-4 border rounded-lg">
                        <h5 class="font-medium">{{ $material->title }}</h5>
                        <p class="text-sm text-gray-600">{{ $material->subject->name ?? '' }}</p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-sm font-medium">Rs. {{ $material->price }}</span>
                            <a href="{{ url('/store') }}" class="text-sm text-primary-600 hover:underline">Purchase</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No paid materials available.</p>
        @endif
    </div>
</x-filament-panels::page>
