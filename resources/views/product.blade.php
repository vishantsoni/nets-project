@extends('layouts.app')

@section('title', $material->title . ' - NETS')

@section('content')
<main class="bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a></li>
                <li><svg class="w-4 h-4 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                <li><a href="{{ route('store') }}" class="hover:text-indigo-600">Store</a></li>
                @if($material->categories->count() > 0)
                    <li><svg class="w-4 h-4 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li><a href="{{ route('store.category', $material->categories->first()->slug) }}" class="hover:text-indigo-600">{{ $material->categories->first()->name }}</a></li>
                @endif
                <li><svg class="w-4 h-4 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                <li class="text-gray-900 truncate max-w-xs">{{ $material->title }}</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10">
            <!-- Product Images -->
            <div class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <div class="relative aspect-[3/4] rounded-xl overflow-hidden bg-gray-100">
                    @if($material->thumbnail)
                        <img id="main-image" src="{{ asset('storage/' . $material->thumbnail) }}" alt="{{ $material->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-7xl">&#128218;</div>
                    @endif
                    
                    <!-- Badges -->
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        @if(! $material->is_paid)
                            <span class="bg-green-500 text-white text-sm font-semibold px-3 py-1 rounded-full">FREE</span>
                        @elseif($material->discount_price && $material->discount_price < $material->price)
                            <span class="bg-red-500 text-white text-sm font-semibold px-3 py-1 rounded-full">
                                {{ round((1 - $material->discount_price / $material->price) * 100) }}% OFF
                            </span>
                        @endif
                        @if($material->type && $material->type !== 'pdf')
                            <span class="bg-indigo-500 text-white text-sm font-medium px-3 py-1 rounded-full">{{ ucfirst($material->type) }}</span>
                        @endif
                    </div>
                </div>
                
                <!-- Thumbnail gallery placeholder -->
                @if($material->gallery_images)
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach($material->gallery_images as $index => $image)
                    <button onclick="changeMainImage('{{ asset('storage/' . $image) }}')" class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 {{ $index === 0 ? 'border-indigo-500' : 'border-transparent hover:border-gray-300' }} transition-colors">
                        <img src="{{ asset('storage/' . $image) }}" alt="{{ $material->title }} - Image {{ $index + 1 }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Product Details -->
            <div class="flex flex-col">
                <div>
                    <!-- Category & Subject Tags -->
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        @if($material->categories->count() > 0)
                            @foreach($material->categories->take(2) as $category)
                                <a href="{{ route('store.category', $category->slug) }}" class="px-3 py-1 text-sm font-medium bg-indigo-50 text-indigo-700 rounded-full hover:bg-indigo-100 transition-colors">{{ $category->name }}</a>
                            @endforeach
                        @endif
                        @if($material->subject)
                            <a href="#" class="px-3 py-1 text-sm font-medium bg-purple-50 text-purple-700 rounded-full hover:bg-purple-100 transition-colors">{{ $material->subject->name }}</a>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">{{ $material->title }}</h1>

                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                        @if($material->edition)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Edition: {{ $material->edition }}
                            </span>
                        @endif
                        @if($material->set_of)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                {{ $material->set_of }}
                            </span>
                        @endif
                        @if($material->download_count > 0)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                {{ number_format($material->download_count) }} downloads
                            </span>
                        @endif
                    </div>

                    <!-- Price Section -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                        <div class="flex items-baseline gap-3">
                            @if($material->discount_price && $material->discount_price < $material->price)
                                <span class="text-3xl font-bold text-indigo-600">₹{{ number_format($material->discount_price, 2) }}</span>
                                <span class="text-xl text-gray-400 line-through">₹{{ number_format($material->price, 2) }}</span>
                                <span class="bg-red-50 text-red-600 px-2 py-1 rounded-full text-sm font-medium">
                                    Save {{ round((1 - $material->discount_price / $material->price) * 100) }}%
                                </span>
                            @elseif(! $material->is_paid)
                                <span class="text-3xl font-bold text-green-600">Free</span>
                            @else
                                <span class="text-3xl font-bold text-gray-900">₹{{ number_format($material->price, 2) }}</span>
                            @endif
                        </div>
                        @if($material->is_paid && $material->discount_price && $material->discount_price < $material->price)
                        <p class="text-sm text-green-600 mt-1">Inclusive of all taxes</p>
                        @endif
                    </div>

                    <!-- Demo File Button -->
                    @if($material->demo_file_path)
                    <div class="mb-6">
                        <a href="{{ asset('storage/' . $material->demo_file_path) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>View Demo File</span>
                        </a>
                       
                    </div>
                    @endif

                    <!-- Description (Rich Text) -->
                    @if($material->description_rich || $material->description)
                    <div class="mb-6" x-data="{ open: true }">
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-indigo-200 hover:shadow-md transition-all duration-200">
                            <button @click="open = !open" class="w-full text-left flex items-center justify-between gap-2 p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900">Description</h2>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-180': open }" x-transition>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-1" class="border-t border-gray-100 bg-gray-50/50">
                                <div class="rich-text p-6">
                                    {!! $material->description_rich ?? $material->description !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Book Structure (Rich Text) -->
                    @if($material->book_structure)
                    <div class="mb-6" x-data="{ open: false }">
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-indigo-200 hover:shadow-md transition-all duration-200">
                            <button @click="open = !open" class="w-full text-left flex items-center justify-between gap-2 p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2m-2 2h-10a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v2"/></svg>
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900">Book Structure</h2>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-180': open }" x-transition>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-1" class="border-t border-gray-100 bg-gray-50/50">
                                <div class="rich-text p-6">
                                    {!! $material->book_structure !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Other Information (Rich Text) -->
                    @if($material->other_information)
                    <div class="mb-6" x-data="{ open: false }">
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-indigo-200 hover:shadow-md transition-all duration-200">
                            <button @click="open = !open" class="w-full text-left flex items-center justify-between gap-2 p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900">Other Information</h2>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-180': open }" x-transition>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-1" class="border-t border-gray-100 bg-gray-50/50">
                                <div class="rich-text p-6">
                                    {!! $material->other_information !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Purchase Section -->
                <div class="mt-auto pt-6 border-t border-gray-100 bg-gradient-to-b from-white to-gray-50 rounded-b-2xl p-6 -mx-6 -mb-6">
                    @auth
                        @if(!$material->is_paid)
                            <form method="POST" action="{{ route('cart.add') }}" class="w-full">
                                @csrf
                                <input type="hidden" name="material_id" value="{{ $material->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Download Free
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('cart.add') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="material_id" value="{{ $material->id }}">
                                <div class="flex items-center gap-4">
                                    <label for="quantity" class="text-sm font-medium text-gray-700">Qty:</label>
                                    <input id="quantity" name="quantity" type="number" min="1" max="10" value="1" class="w-20 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-center">
                                </div>
                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    Add to Cart
                                </button>
                            </form>
                            
                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2.5 rounded-xl border border-green-100 hover:bg-green-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span class="font-medium text-sm">Secure Payment</span>
                                </div>
                                <div class="flex items-center gap-2 bg-indigo-50 text-indigo-700 px-4 py-2.5 rounded-xl border border-indigo-100 hover:bg-indigo-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span class="font-medium text-sm">Instant Download</span>
                                </div>
                                <div class="flex items-center gap-2 bg-purple-50 text-purple-700 px-4 py-2.5 rounded-xl border border-purple-100 hover:bg-purple-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="font-medium text-sm">Lifetime Access</span>
                                </div>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 px-6 rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            Log in to Purchase
                        </a>
                        <p class="text-center text-sm text-gray-500 mt-3">Create an account to access your purchases anytime</p>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedMaterials->count() > 0)
        <section class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedMaterials as $related)
                <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="relative aspect-[3/4] bg-gray-100 overflow-hidden">
                        @if($related->thumbnail)
                            <img src="{{ asset('storage/' . $related->thumbnail) }}" alt="{{ $related->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">
                            <a href="{{ route('product.show', $related->id) }}" class="hover:text-indigo-600">{{ $related->title }}</a>
                        </h3>
                        <div class="flex items-center gap-2">
                            @if($related->discount_price && $related->discount_price < $related->price)
                                <span class="text-lg font-bold text-indigo-600">₹{{ number_format($related->discount_price, 2) }}</span>
                                <span class="text-gray-400 line-through text-sm">₹{{ number_format($related->price, 2) }}</span>
                            @elseif(! $related->is_paid)
                                <span class="text-lg font-bold text-green-600">Free</span>
                            @else
                                <span class="text-lg font-bold text-gray-900">₹{{ number_format($related->price, 2) }}</span>
                            @endif
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    function changeMainImage(src) {
        document.getElementById('main-image').src = src;
        document.querySelectorAll('[onclick^="changeMainImage"]').forEach(btn => {
            btn.classList.remove('border-indigo-500');
            btn.classList.add('border-transparent', 'hover:border-gray-300');
        });
        event.target.closest('button').classList.add('border-indigo-500');
        event.target.closest('button').classList.remove('border-transparent', 'hover:border-gray-300');
    }

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

    function showNotification(message, type = 'info') {
        const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-indigo-500' };
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.classList.remove('translate-x-full'), 100);
        setTimeout(() => { notification.classList.add('translate-x-full'); setTimeout(() => notification.remove(), 300); }, 3000);
    }

    function updateCartCount(count) {
        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) { cartBadge.textContent = count; cartBadge.classList.remove('hidden'); }
    }
</script>
@endpush