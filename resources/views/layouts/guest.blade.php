<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="font-sans text-gray-900 antialiased bg-sky-100">
    {{-- Container utama diubah untuk menggunakan CSS Grid --}}
    <div class="min-h-screen grid grid-cols-1 md:grid-cols-2 gap-8 items-center justify-items-center p-4 md:p-6">

        <!-- Sisi Kiri: Logo/Ilustrasi -->
        <div class="flex justify-center items-center">
            <img src="/images/Eult.png" alt="Illustration" class="w-64 md:w-full md:max-w-md">
        </div>

        <!-- Sisi Kanan: Kartu Form Login -->
        <div class="w-full max-w-md p-6 sm:p-8 bg-white shadow-lg overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
