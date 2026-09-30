@extends('layouts.app')

@section('title', 'Store - NETS')

@section('content')
<!-- Store Header with Category Navigation -->
<section class="bg-white py-6 border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Study Materials Store</h1>
                <p class="text-gray-600 mt-1 text-sm">Browse and purchase high-quality educational materials</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <select id="category-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <select id="sort-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-sm">
                    <option value="latest">Latest</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Category Mega Navigation -->
@if($categories->where('parent_id', null)->count() > 0)
<section class="bg-gray-50 py-4 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2 md:gap-4 overflow-x-auto pb-2">
            @foreach($categories->where('parent_id', null) as $parentCategory)
                <div class="relative group">
                    <button class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors whitespace-nowrap">
                        {{ $parentCategory->name }}
                    </button>
                    @if($parentCategory->children->count() > 0)
                    <div class="absolute left-0 top-full mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-2">
                            @foreach($parentCategory->children as $child)
                                <a href="{{ route('store.category', $child->slug) }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                    {{ $child->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Categories Section -->
@if($featuredCategories->count() > 0)
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredCategories as $category)
            <a href="{{ route('store.category', $category->slug) }}" class="group relative aspect-[4/3] rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                @if($category->thumbnail)
                    <img src="{{ asset('storage/' . $category->thumbnail) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                        <svg class="w-16 h-16 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4">
                    <h3 class="text-lg font-semibold text-white">{{ $category->name }}</h3>
                    <p class="text-white/80 text-sm mt-1">{{ $category->study_materials_count ?? 0 }} products</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Products Grid -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(request('category'))
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    Category: {{ $currentCategory->name ?? ucfirst(request('category')) }}
                </h2>
                @if($currentCategory->description)
                    <p class="text-gray-600 mt-2">{{ $currentCategory->description }}</p>
                @endif
            </div>
        @endif

        <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($materials as $material)
                <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                    <div class="relative aspect-[3/4] bg-gray-100 overflow-hidden">
                        @if($material->thumbnail)
                            <img src="{{ asset('storage/' . $material->thumbnail) }}" alt="{{ $material->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                            @if(! $material->is_paid)
                                <span class="bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded-full">FREE</span>
                            @elseif($material->discount_price && $material->discount_price < $material->price)
                                <span class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    {{ round((1 - $material->discount_price / $material->price) * 100) }}% OFF
                                </span>
                            @endif
                            @if($material->type && $material->type !== 'pdf')
                                <span class="bg-indigo-500 text-white text-xs font-medium px-2 py-1 rounded-full">{{ ucfirst($material->type) }}</span>
                            @endif
                        </div>

                        <!-- Quick Add to Cart -->
                        <button onclick="addToCart({{ $material->id }})" class="absolute bottom-3 right-3 bg-white/95 hover:bg-white text-gray-900 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </button>
                        
                        <!-- Wishlist -->
                        <button onclick="toggleWishlist({{ $material->id }})" class="absolute bottom-3 left-3 bg-white/95 hover:bg-white text-gray-900 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-4">
                        <!-- Category & Subject Tags -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-2">
                            @if($material->categories->count() > 0)
                                <span class="px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded">{{ $material->categories->first()->name }}</span>
                            @endif
                            @if($material->subject)
                                <span class="px-2 py-0.5 text-xs font-medium bg-purple-50 text-purple-700 rounded">{{ $material->subject->name }}</span>
                            @endif
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                            <a href="{{ route('product.show', $material->id) }}">{{ $material->title }}</a>
                        </h3>
                        
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ Str::limit(strip_tags($material->description ?? ''), 100) }}</p>
                        
                        <!-- Meta Info -->
                        @if($material->edition || $material->set_of)
                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400 mb-3">
                            @if($material->edition)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $material->edition }}
                                </span>
                            @endif
                            @if($material->set_of)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    {{ $material->set_of }}
                                </span>
                            @endif
                        </div>
                        @endif
                        
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2">
                                @if($material->discount_price && $material->discount_price < $material->price)
                                    <span class="text-lg font-bold text-indigo-600">₹{{ number_format($material->discount_price, 2) }}</span>
                                    <span class="text-gray-400 line-through text-sm">₹{{ number_format($material->price, 2) }}</span>
                                @elseif(! $material->is_paid)
                                    <span class="text-lg font-bold text-green-600">Free</span>
                                @else
                                    <span class="text-lg font-bold text-gray-900">₹{{ number_format($material->price, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('product.show', $material->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View Details</a>
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

    // Wishlist toggle
    function toggleWishlist(materialId) {
        // TODO: Implement wishlist functionality
        showNotification('Wishlist feature coming soon!', 'info');
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

    // Notification helper
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-indigo-500'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Update cart count
    function updateCartCount(count) {
        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.classList.remove('hidden');
        }
    }
</script>
@endpush