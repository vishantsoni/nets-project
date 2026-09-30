@extends('layouts.app')

@section('content')
    <!-- Hero Carousel -->
    @if($heroes->isNotEmpty())
        <section x-data="heroCarousel(@js($heroes))"
            class="relative text-white overflow-hidden min-h-[400px] lg:min-h-[500px]"
            x-init="init()">
            <!-- Slide -->
            <template x-for="(hero, index) in heroes" :key="index">
                <div
                    class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                    :class="{ 'opacity-100': current === index, 'opacity-0': current !== index }">
                     <div
                         class="absolute inset-0 bg-black/30">
                     </div>

                    <template x-if="hero.background_image">
                        <img :src="hero.background_image ? '{{ asset('storage') }}/' + hero.background_image : ''"
                            alt=""
                            class="absolute inset-0 w-full h-full object-cover">
                    </template>
                    <template x-if="!hero.background_image">
                        <div
                            class="absolute inset-0"
                            :class="hero.background_gradient || 'bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500'">
                        </div>
                    </template>

                    <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>

                    <div
                        class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full min-h-[400px] lg:min-h-[500px] flex items-center justify-center text-center">
                        <div class="max-w-4xl mx-auto">
                            <h1
                                class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6"
                                x-text="hero.title">
                            </h1>
                            <p
                                class="text-lg sm:text-xl text-white/90 max-w-3xl mx-auto mb-10"
                                x-text="hero.subtitle">
                            </p>
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <template x-if="hero.primary_button_text && hero.primary_button_url">
                                    <a :href="hero.primary_button_url"
                                        class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-white/90 transition-colors shadow-lg"
                                        x-text="hero.primary_button_text">
                                    </a>
                                </template>
                                <template x-if="hero.secondary_button_text && hero.secondary_button_url">
                                    <a :href="hero.secondary_button_url"
                                        class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors"
                                        x-text="hero.secondary_button_text">
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Navigation Arrows -->
            <button @click="prev()"
                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white/20 text-white p-2 rounded-full transition-colors z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button @click="next()"
                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/10 hover:bg-white/20 text-white p-2 rounded-full transition-colors z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Dots -->
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
                <template x-for="(hero, index) in heroes" :key="index">
                    <button @click="goTo(index)"
                        class="w-3 h-3 rounded-full transition-all duration-300"
                        :class="{ 'bg-white w-6': current === index, 'bg-white/40': current !== index }">
                    </button>
                </template>
            </div>
        </section>
    @else
        <section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white py-20 lg:py-32">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        NETS - Your Gateway to <span class="text-yellow-200">Academic Excellence</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-white/90 max-w-3xl mx-auto mb-10">
                        Comprehensive Study Materials, Online Examinations, E-Commerce Platform, and B2B Solutions for
                        educational institutes.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('filament.student.resources.examinations.index') }}"
                            class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-white/90 transition-colors shadow-lg">
                            Start Exam
                        </a>
                        <a href="{{ route('store') }}"
                            class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors">
                            Browse Materials
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Featured Categories Grid -->
    @if($featuredCategories->count() > 0)
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Popular Categories</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Explore our curated collection of study materials</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($featuredCategories as $category)
                        <a href="{{ route('store.category', $category->slug) }}"
                            class="group relative aspect-[4/3] rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                            @if($category->thumbnail)
                                <img src="{{ asset('storage/' . $category->thumbnail) }}" alt="{{ $category->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div
                                    class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-5">
                                <h3 class="text-lg font-semibold text-white">{{ $category->name }}</h3>
                                <p class="text-white/80 text-sm mt-1">{{ $category->study_materials_count ?? 0 }} products</p>
                                <span class="inline-flex items-center gap-1 mt-3 text-white/90 text-sm font-medium">
                                    Explore
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    @php
        $sectionIndex = 0;
    @endphp

    <!-- Category-wise Featured Products -->
    @foreach($mainCategories as $mainCategory)
        @php
            $categoryMaterials = \App\Models\StudyMaterial::where('is_published', true)
                ->whereHas('categories', fn($q) => $q->where('parent_id', $mainCategory->id))
                ->with(['subject', 'categories'])
                ->latest()
                ->limit(8)
                ->get();
        @endphp

        @if($categoryMaterials->count() > 0)
            @php
                $sectionIndex++;
            @endphp
            <section class="py-12 {{  $sectionIndex % 2 === 0 ? 'bg-green-50' : '' }}">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $mainCategory->name }}</h2>
                            @if($mainCategory->description)
                                <p class="text-gray-600 mt-1">{{ $mainCategory->description }}</p>
                            @endif
                        </div>
                        <a href="{{ route('store.category', $mainCategory->slug) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1.5 text-sm">
                            View All
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($categoryMaterials as $material)
                            <article
                                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                                <div class="relative aspect-[3/4] bg-gray-100 overflow-hidden">
                                    @if($material->thumbnail)
                                        <img src="{{ asset('storage/' . $material->thumbnail) }}" alt="{{ $material->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                        @if(!$material->is_paid)
                                            <span class="bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded-full">FREE</span>
                                        @elseif($material->discount_price && $material->discount_price < $material->price)
                                            <span class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                                                {{ round((1 - $material->discount_price / $material->price) * 100) }}% OFF
                                            </span>
                                        @endif
                                    </div>

                                    <button onclick="addToCart({{ $material->id }})"
                                        class="absolute bottom-3 right-3 bg-white/95 hover:bg-white text-gray-900 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <div class="flex flex-wrap items-center gap-1.5 mb-2">
                                        @if($material->categories->count() > 0)
                                            <span
                                                class="px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded">{{ $material->categories->first()->name }}</span>
                                        @endif
                                        @if($material->subject)
                                            <span
                                                class="px-2 py-0.5 text-xs font-medium bg-purple-50 text-purple-700 rounded">{{ $material->subject->name }}</span>
                                        @endif
                                    </div>
                                    <h3
                                        class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                        <a href="{{ route('product.show', $material->id) }}">{{ $material->title }}</a>
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-3 line-clamp-2">
                                        {{ Str::limit(strip_tags($material->description ?? ''), 100) }}</p>

                                    @if($material->edition || $material->set_of)
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400 mb-3">
                                            @if($material->edition)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $material->edition }}
                                                </span>
                                            @endif
                                            @if($material->set_of)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                    {{ $material->set_of }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        <div class="flex items-center gap-2">
                                            @if($material->discount_price && $material->discount_price < $material->price)
                                                <span
                                                    class="text-lg font-bold text-indigo-600">₹{{ number_format($material->discount_price, 2) }}</span>
                                                <span
                                                    class="text-gray-400 line-through text-sm">₹{{ number_format($material->price, 2) }}</span>
                                            @elseif(!$material->is_paid)
                                                <span class="text-lg font-bold text-green-600">Free</span>
                                            @else
                                                <span
                                                    class="text-lg font-bold text-gray-900">₹{{ number_format($material->price, 2) }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('product.show', $material->id) }}"
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View Details</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
        </section>
    @endif
    @endforeach

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Why Choose NETS?</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Complete educational ecosystem for students, teachers,
                    and institutes</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Online Examinations</h3>
                    <p class="text-gray-600">Create and manage exams with multiple question types, timer, auto-evaluation,
                        and instant results.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Study Materials</h3>
                    <p class="text-gray-600">Access comprehensive study materials, notes, and resources for various subjects
                        and topics.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">E-Commerce Store</h3>
                    <p class="text-gray-600">Purchase study materials, books, and educational resources with secure payment
                        options.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-shadow border border-gray-100">
                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">B2B Solutions</h3>
                    <p class="text-gray-600">Enquiry management and partnership opportunities for educational institutes and
                        organizations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-white mb-2">10,000+</div>
                    <div class="text-indigo-200">Active Students</div>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-white mb-2">500+</div>
                    <div class="text-indigo-200">Partner Institutes</div>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-white mb-2">50,000+</div>
                    <div class="text-indigo-200">Questions Bank</div>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-white mb-2">99%</div>
                    <div class="text-indigo-200">Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">Ready to Start Your Journey?</h2>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto mb-10">Join thousands of students and institutes who trust
                NETS for their educational needs.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                    Get Started Free
                </a>
                <a href="{{ route('b2b-enquiry') }}"
                    class="bg-transparent border-2 border-gray-600 hover:border-gray-400 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                    Business Enquiry
                </a>
            </div>
        </div>
    </section>
@endsection