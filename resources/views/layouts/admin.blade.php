<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SmartLab Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $baseClass = "flex items-center px-6 py-3 transition-all duration-200 group border-l-4";
        $activeClass = "bg-slate-800 border-amber-500 text-white";
        $inactiveClass = "text-slate-400 hover:bg-slate-800 hover:text-white border-transparent";
        $iconActive = "text-amber-500";
        $iconInactive = "text-slate-500 group-hover:text-white";
        $menus = [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon'  => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'
            ],
            [
                'label' => 'Manajemen User',
                'route' => 'admin.manajemen-user',
                'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            ],
            [
                'label' => 'Booking',
                'route' => 'admin.booking.dashboard',
                'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
            ],
            ['header' => 'Manajemen Lab'],
                [
                    'label' => 'Inventaris PC',
                    'route' => 'admin.inventory',
                    'icon' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
                ],
                [
                    'label' => 'Kelola Data Lab',
                    'route' => 'admin.clusters.index',
                    'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'
                ],
                [
                    'label' => 'Data Inventaris',
                    'route' => 'admin.inventaris.index',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'// Icon: Squares 2x2 / View Grid (Heroicons)
                ]
        ];
    @endphp
</head>

<body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden relative">
            
            {{-- TOPBAR (Mobile Toggle & User Info) --}}
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm z-10">
                
                {{-- Tombol Hamburger (Mobile Only) --}}
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                {{-- Judul Halaman (Slot Header) --}}
                <h2 class="text-xl font-semibold text-gray-800 hidden md:block">
                    {{ $header ?? 'Panel Admin' }}
                </h2>

                {{-- Profil Admin Pojok Kanan --}}
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-bold text-gray-700">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500">Administrator</div>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-slate-900 text-amber-500 flex items-center justify-center font-bold text-lg border-2 border-slate-200">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            {{-- MAIN SLOT --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                {{-- Overlay Mobile --}}
                <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black bg-opacity-50 md:hidden" style="display: none;"></div>
                
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>
    </div>
</body>
</html>