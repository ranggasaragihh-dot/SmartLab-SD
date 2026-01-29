<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SmartLab Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- 
        ========================================================================
        KONFIGURASI MENU & STYLE (EDIT DISINI UNTUK MENAMBAH MENU)
        ========================================================================
    --}}
    @php
        // 1. DEFINISI STYLE (Agar tidak menulis class panjang berulang kali)
        $baseClass = "flex items-center px-6 py-3 transition-all duration-200 group border-l-4";
        
        // Style saat menu AKTIF (Slate-800 + Border Amber + Teks Putih)
        $activeClass = "bg-slate-800 border-amber-500 text-white";
        
        // Style saat menu TIDAK AKTIF (Slate-400 + Hover Effect)
        $inactiveClass = "text-slate-400 hover:bg-slate-800 hover:text-white border-transparent";
        
        // Style Icon (Aktif warna Amber, Tidak Aktif warna Slate)
        $iconActive = "text-amber-500";
        $iconInactive = "text-slate-500 group-hover:text-white";

        // 2. DAFTAR MENU (Tambahkan array baru disini untuk menu baru)
        $menus = [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon'  => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'
            ],
            [
                'label' => 'Manajemen User',
                'route' => 'admin.manajemen-user',
                'icon'  => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'
            ],
            ['header' => 'Manajemen Lab'],
                [
                    'label' => 'Inventaris PC',
                    'route' => 'admin.inventory',
                    'icon'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
                ],
                [
                    'label' => 'Kelola Data Lab',
                    'route' => 'admin.clusters.index',
                    'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'
                ]
        ];
    @endphp
</head>

<body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        {{-- ================================================= --}}
        {{--                SIDEBAR ADMIN (DARK)               --}}
        {{-- ================================================= --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transition-transform duration-300 transform md:relative md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <div class="flex flex-col h-full">
                
                {{-- 1. LOGO HEADER --}}
                <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950 shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center text-slate-900 font-bold">S</div>
                        <h1 class="text-lg font-bold tracking-wider">SMARTLAB <span class="text-xs text-amber-500 block -mt-1">ADMINISTRATOR</span></h1>
                    </div>
                </div>

                {{-- 2. MENU NAVIGASI (LOOPING) --}}
                <nav class="flex-1 overflow-y-auto py-6 space-y-1">
                    
                    @foreach($menus as $menu)   
                        @if(isset($menu['header']))
                            
                            {{-- Tampilan Label Header (Kecil, Uppercase, Slate-500) --}}
                            <div class="px-6 mt-12 mb-3 text-xs font-bold text-slate-500 uppercase tracking-widest">
                                {{ $menu['header'] }}
                            </div>

                        @else
                            @php
                                // Cek apakah route saat ini cocok dengan menu ini
                                $isActive = request()->routeIs($menu['route']);
                            @endphp

                            <a href="{{ route($menu['route']) }}" wire:navigate
                            class="{{ $baseClass }} {{ $isActive ? $activeClass : $inactiveClass }}">
                                
                                {{-- Icon SVG --}}
                                <svg class="w-5 h-5 mr-3 {{ $isActive ? $iconActive : $iconInactive }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $menu['icon'] }}"></path>
                                </svg>
                                
                                {{-- Label Text --}}
                                {{ $menu['label'] }}
                            </a>
                        @endif
                    @endforeach

                </nav>

                {{-- 3. TOMBOL LOGOUT (Fixed di Bawah) --}}
                <div class="p-4 border-t border-slate-800 bg-slate-900">
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <a href="{{ route('logout') }}" 
                           @click.prevent="$root.submit();" 
                           class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-400 bg-slate-950/50 hover:bg-red-900/20 hover:text-red-300 rounded-lg transition-colors cursor-pointer border border-transparent hover:border-red-900/30">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar Sistem
                        </a>
                    </form>
                    <div class="mt-4 text-xs text-center text-slate-600">
                        &copy; 2026 SmartLab System
                    </div>
                </div>

            </div>
        </aside>

        {{-- ================================================= --}}
        {{--                  KONTEN UTAMA                     --}}
        {{-- ================================================= --}}
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
        </div>

    </div>

</body>
</html>