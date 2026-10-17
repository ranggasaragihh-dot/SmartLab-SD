<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.layouts.head')
    <title>{{ config('app.name', 'Lab Booking') }}</title>
</head>

<body class="font-sans antialiased bg-gray-100 text-slate-900">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen overflow-hidden">
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <main>
                {{ $slot }}
            </main>
            @include('layouts.footer')
        </div>

    </div>
</body>
</html>