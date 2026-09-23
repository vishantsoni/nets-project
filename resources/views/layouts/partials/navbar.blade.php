@php
    $isAuthenticated = auth()->check();
    $user = $isAuthenticated ? auth()->user() : null;
    $cartCount = $isAuthenticated ? \App\Models\CartItem::where("user_id", $user->id)->sum("quantity") : 0;
@endphp

<nav class="bg-white shadow-sm border-b">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <div class="flex items-center space-x-4">
                <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900">NETS</a>
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
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
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
