<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NETS') }} - @yield('title', 'Study Materials, E-Commerce, Examinations')</title>
    <meta name="description" content="@yield('description', 'NETS - Comprehensive Study Material, E-Commerce, B2B Query Management, and Online Examination System')">
    <meta name="keywords" content="@yield('keywords', 'study materials, online exams, e-commerce, education platform, coaching institute')">
    <meta name="author" content="NETS">
    <meta property="og:title" content="{{ config('app.name', 'NETS') }} - @yield('title', 'Study Materials, E-Commerce, Examinations')">
    <meta property="og:description" content="@yield('description', 'NETS - Comprehensive Study Material, E-Commerce, B2B Query Management, and Online Examination System')">
    <meta property="og:image" content="{{ asset('images/logo_nets.jpeg') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name', 'NETS') }} - @yield('title', 'Study Materials, E-Commerce, Examinations')">
    <meta name="twitter:description" content="@yield('description', 'NETS - Comprehensive Study Material, E-Commerce, B2B Query Management, and Online Examination System')">
    <meta name="twitter:image" content="{{ asset('images/logo_nets.jpeg') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_nets.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_nets.jpeg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900" rel="stylesheet">
</head>
<body class="font-sans antialiased">
    @include('layouts.partials.navbar')

    @yield('content')

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
