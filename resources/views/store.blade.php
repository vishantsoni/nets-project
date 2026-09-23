@extends('layouts.app')

@section('title', 'Store - NETS')

@section('content')
<!-- Store Header -->
<section class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Study Materials Store</h1>
                <p class="text-gray-600 mt-2">Browse and purchase high-quality educational materials</p>
            </div>
            <div class="flex items-center gap-4">
                <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <select id="sort-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="latest">Latest</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Products Grid -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($materials as $material)
                <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                    <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
                        @if($material->thumbnail)
                            <img src="{{ asset('storage/' . $material->thumbnail) }}" alt="{{ $material->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        @if($material->is_free)
                            <span class="absolute top-3 left-3 bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded-full">FREE</span>
                        @elseif($material->discount_price)
                            <span class="absolute top-3 left-3 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">SALE</span>
                        @endif
                        <button onclick="addToCart({{ $material->id }})" class="absolute bottom-3 right-3 bg-white/90 hover:bg-white text-gray-900 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-2">
                            @if($material->category)
                                <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">{{ $material->category->name }}</span>
                            @endif
                            @if($material->subject)
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">{{ $material->subject->name }}</span>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $material->title }}</h3>
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $material->short_description }}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($material->discount_price)
                                    <span class="text-xl font-bold text-indigo-600">${{ number_format($material->discount_price, 2) }}</span>
                                    <span class="text-gray-400 line-through">${{ number_format($material->price, 2) }}</span>
                                @elseif($material->is_free)
                                    <span class="text-xl font-bold text-green-600">Free</span>
                                @else
                                    <span class="text-xl font-bold text-gray-900">${{ number_format($material->price, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('store.show', $material->slug) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View Details</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No materials found</h3>
                    <p class="text-gray-500">Try adjusting your filters or check back later.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        {{ $materials->links() }}
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Add to cart functionality
    function addToCart(materialId) {
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ material_id: materialId, quantity: 1 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cart_count);
                showNotification('Added to cart!', 'success');
            } else {
                showNotification(data.message || 'Failed to add to cart', 'error');
            }
        })
        .catch(() => showNotification('An error occurred', 'error'));
    }

    // Category filter
    document.getElementById('category-filter')?.addEventListener('change', function() {
        const url = new URL(window.location);
        if (this.value) {
            url.searchParams.set('category', this.value);
        } else {
            url.searchParams.delete('category');
        }
        window.location.href = url.toString();
    });

    // Sort filter
    document.getElementById('sort-filter')?.addEventListener('change', function() {
        const url = new URL(window.location);
        if (this.value !== 'latest') {
            url.searchParams.set('sort', this.value);
        } else {
            url.searchParams.delete('sort');
        }
        window.location.href = url.toString();
    });
</script>
@endpush