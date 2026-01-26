<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartLab Booking</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased h-screen w-screen overflow-hidden bg-gray-100 x-data="{ sidebarOpen: false }">
    {{-- Slot ini akan diisi oleh Livewire --}}
    {{ $slot }}
</body>
</html>