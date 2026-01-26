<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartLab Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">
    
    <div class="flex h-screen overflow-hidden">
        
        {{-- ================= SIDEBAR ADMIN (GELAP) ================= --}}
        <aside class="w-64 bg-slate-900 text-white flex flex-col transition-all duration-300 z-30 fixed md:relative h-full"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            {{-- Header Sidebar --}}
            <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950">
                <h1 class="text-xl font-bold tracking-wider text-amber-500">SMARTLAB <span class="text-white text-xs">ADMIN</span></h1>
            </div>

            <!-- {{-- Menu Navigasi --}}
            <nav class="flex-1 overflow-y-auto py-4 space-y-1">
                {{-- 1. Dashboard --}}
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-6 py-3 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 border-l-4 border-amber-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>

                {{-- 2. Inventaris --}}
                <a href="{{ route('admin.inventory') }}" 
                   class="flex items-center px-6 py-3 transition-colors {{ request()->routeIs('admin.inventory') ? 'bg-slate-800 border-l-4 border-amber-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                    Inventaris PC
                </a>

                <a href="{{ route('admin.inventory') }}" 
                   class="flex items-center px-6 py-3 transition-colors {{ request()->routeIs('admin.inventory') ? 'bg-slate-800 border-l-4 border-amber-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                    Denah PC
                </a>
            </nav> -->

            {{-- Footer Logout (Alpine JS Form) --}}
            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <a href="{{ route('logout') }}" 
                       @click.prevent="$root.submit();" 
                       class="flex items-center w-full px-4 py-2 text-sm text-red-400 hover:bg-slate-800 hover:text-red-300 rounded transition-colors cursor-pointer">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </a>
                </form>
            </div>
        </aside>

        {{-- ================= KONTEN KANAN ================= --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            {{-- Topbar --}}
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shadow-sm z-10">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ $header ?? 'Dashboard' }}
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-600">Administrator</span>
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold">A</div>
                </div>
            </header>

            {{-- Slot Halaman (Dashboard/Inventory akan muncul di sini) --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>