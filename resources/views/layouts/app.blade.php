<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lab Booking') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100">
    
    {{-- HIERARKI 1: Wrapper Paling Luar (Root) --}}
    {{-- WAJIB: x-data DITARUH DI SINI agar melingkupi Sidebar & Konten --}}
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        {{-- HIERARKI 2: Include Sidebar --}}
        {{-- Karena berada DI DALAM div x-data, file ini BISA BACA sidebarOpen --}}
        @include('layouts.admin')

        {{-- HIERARKI 3: Konten Utama & Header --}}
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            
            {{-- Header dengan Tombol Burger --}}
            <!-- <header class="md:hidden ...">
                <div class="flex items-center gap-3">
                    
                    {{-- PANGGIL GLOBAL STORE --}}
                    <button @click="$store.layout.open()" class="...">
                        <svg class="...">...</svg>
                    </button>

                    <span class="font-bold">Lab Booking</span>
                </div>
            </header> -->

            <main>
                {{ $slot }}
            </main>
        </div>

    </div>
</body>
</html>