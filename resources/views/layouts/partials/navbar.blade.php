@php
    $isAuthenticated = auth()->check();
    $user = $isAuthenticated ? auth()->user() : null;
    $cartCount = $isAuthenticated ? \App\Models\CartItem::where("user_id", $user->id)->sum("quantity") : 0;
    $mainCategories = \App\Models\Category::whereNull('parent_id')
        ->where('is_active', true)
        ->with('children')
        ->orderBy('name')
        ->get();
@endphp

<nav class="bg-white shadow-sm border-b sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <div class="flex items-center space-x-4">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo_nets.jpeg') }}" alt="NETS Logo" class="h-10 w-auto">
                    <span class="text-xl font-bold text-gray-900">NETS</span>
                </a>
                
                <!-- Mega Menu Navigation -->
                <div class="hidden lg:flex items-center space-x-1" x-data="{ openCategory: null }">
                    @foreach($mainCategories as $index => $category)
                    <div class="relative group" @mouseenter="openCategory = {{ $index }}" @mouseleave="openCategory = null">
                        <button 
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors whitespace-nowrap flex items-center gap-1.5"
                            @click="openCategory = openCategory === {{ $index }} ? null : {{ $index }}"
                        >
                            {{ $category->name }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        @if($category->children->count() > 0)
                        <div 
                            x-show="openCategory === {{ $index }}"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="absolute left-0 top-full mt-1 w-72 bg-white rounded-xl shadow-xl border border-gray-100 py-3 z-50"
                            @click.outside="openCategory = null"
                        >
                            <div class="grid grid-cols-1 gap-1 p-2">
                                @foreach($category->children as $child)
                                <a href="{{ route('store.category', $child->slug) }}" class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-lg transition-colors flex items-center gap-2">
                                    @if($child->icon)
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $child->icon }}"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @endif
                                    {{ $child->name }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                
                <!-- Mobile Menu Items (simplified) -->
                <div class="hidden md:flex space-x-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600">Home</a>
                    <a href="{{ route('store') }}" class="text-gray-700 hover:text-primary-600">Store</a>
                    <a href="{{ route('b2b-enquiry') }}" class="text-gray-700 hover:text-primary-600">B2B Enquiry</a>
                    <a href="{{ route('contact') }}" class="text-gray-700 hover:text-primary-600">Contact</a>
                    @if($isAuthenticated && $user->hasRole('student'))
                        <a href="{{ url('/student') }}" class="text-gray-700 hover:text-primary-600">Student Panel</a>
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-4">
                @if($isAuthenticated)
                    <a href="{{ route('cart') }}" class="relative text-gray-700 hover:text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.89 2M7 12h10a2 2 0 012 2v4a2 2 0 01-2 2H5.41a2 2 0 01-2-2V8M7 12l-3-10m0 0L4 4m0 0L3 3m1 0h.01M7 12l3-3 4 4 3-3M17 12h3a2 2 0 012 2v4a2 2 0 01-2 2h-3m-4 0h.01M9 21a2 2 0 100-4 2 2 0 000 4zm12 0a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center cart-count">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-primary-600">
                            <span class="hidden md:inline">{{ $user->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                            <a href="{{ url('/student/profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary-600 text-sm">Login</a>
                    <a href="{{ route('register') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-700">Register</a>
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Mega Menu (Alpine.js) -->
<div x-data="{ mobileMenuOpen: false, openCategory: null }" class="lg:hidden">
    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-700" aria-label="Toggle menu">
        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="absolute top-full left-0 right-0 bg-white border-t border-gray-100 shadow-lg z-40">
        <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
            <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">Home</a>
            <a href="{{ route('store') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">Store</a>
            
            @foreach($mainCategories as $index => $category)
            <div class="border-t border-gray-100 pt-2">
                <button @click="openCategory = openCategory === {{ $index }} ? null : {{ $index }}" class="w-full flex items-center justify-between px-3 py-2 text-gray-700 font-medium">
                    {{ $category->name }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-180': openCategory === {{ $index }} }" x-transition>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openCategory === {{ $index }}" x-transition class="ml-4 mt-2 space-y-1 pl-2 border-l-2 border-gray-100">
                    @foreach($category->children as $child)
                    <a href="{{ route('store.category', $child->slug) }}" class="block px-3 py-1.5 text-sm text-gray-600 hover:text-indigo-600 hover:bg-gray-50 rounded"> {{ $child->name }} </a>
                    @endforeach
                </div>
            </div>
            @endforeach
            
            <a href="{{ route('b2b-enquiry') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">B2B Enquiry</a>
            <a href="{{ route('contact') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">Contact</a>
            @if($isAuthenticated && $user->hasRole('student'))
                <a href="{{ url('/student') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">Student Panel</a>
            @endif
            @if($isAuthenticated)
                <form method="POST" action="{{ route('logout') }}" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">Logout</button>
                </form>
            @else
                <div class="pt-2 space-y-2">
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">Login</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 bg-primary-600 text-white text-center rounded-lg hover:bg-primary-700">Register</a>
                </div>
            @endif
        </div>
    </div>
</div>
