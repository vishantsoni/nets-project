<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NETS') }} - @yield('title', 'Study Materials, E-Commerce, Examinations')</title>
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
