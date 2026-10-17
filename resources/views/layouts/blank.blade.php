<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.layouts.head')
    <title>Lab Display System</title>
    @livewireStyles
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-950 text-white antialiased overflow-hidden">
    {{ $slot }}
    @livewireScripts
</body>
</html>