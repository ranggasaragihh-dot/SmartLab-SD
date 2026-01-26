{{-- 
    FILE: resources/views/layouts/sidebar.blade.php
    
    CATATAN PENTING:
    File ini TIDAK BOLEH memiliki 'x-data="{...}"'.
    Variable 'sidebarOpen' dikendalikan sepenuhnya dari file induk (app.blade.php).
--}}
<div x-data="{ open: false }" @toggle-sidebar.window="open = !open">
{{-- 1. BACKDROP GELAP (Overlay) --}}
    <div 
        x-show="open"
        x-transition.opacity
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden"
        style="display: none;"
    ></div>

    {{-- 2. SIDEBAR UTAMA --}}
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-64 bg-blue-700 text-white flex flex-col transition-transform duration-300 transform 
            -translate-x-full md:translate-x-0 md:static md:h-screen shadow-2xl flex-shrink-0 h-full"
        :class="open ? 'translate-x-0' : ''"
    >
        
        {{-- Header Profile & Tombol Close --}}
        <div class="h-20 flex items-center justify-between px-6 bg-blue-800 border-b border-blue-600 relative">
            <div class="flex items-center gap-3 overflow-hidden">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full bg-blue-500 border-2 border-white flex-shrink-0 flex items-center justify-center font-bold shadow-md">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                
                {{-- Nama User --}}
                <div class="flex flex-col min-w-0">
                    <h4 class="font-bold text-sm truncate w-32 leading-tight">{{ Auth::user()->name ?? 'Mahasiswa' }}</h4>
                    <p class="text-[10px] text-blue-200 uppercase tracking-wider font-semibold">Informatika</p>
                </div>
            </div>

            {{-- TOMBOL CLOSE (Hanya Muncul di HP) --}}
            <button @click="open = false" class="md:hidden text-blue-300 hover:text-white transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Navigation List --}}
        <nav class="flex-1 overflow-y-auto py-6 space-y-1">
            <div class="px-6 mb-3 text-xs font-bold text-blue-300 uppercase tracking-widest opacity-80">
                Daftar Ruangan
            </div>

            @foreach($clusters as $cluster)
                <button 
                    wire:click="$set('selectedClusterId', {{ $cluster->id }})"
                    @click="open = false"
                    class="w-full text-left px-6 py-3 flex items-center gap-3 transition-all duration-200 border-l-4 group relative
                    {{ $selectedClusterId == $cluster->id 
                        ? 'bg-blue-800/50 border-amber-400 text-white font-bold shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)]' 
                        : 'border-transparent text-blue-100 hover:bg-blue-600 hover:text-white hover:border-blue-400' }}"
                >
                    {{-- Icon Ruangan --}}
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    
                    <span class="truncate">{{ $cluster->name }}</span>
                    
                    {{-- Indikator Aktif --}}
                    @if($selectedClusterId == $cluster->id)
                        <span class="absolute right-4 w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                    @endif
                </button>
            @endforeach
        </nav>

        {{-- Footer Logout --}}
        <div class="p-4 bg-blue-800 border-t border-blue-600">
            {{-- HAPUS x-data DARI SINI, GANTI DENGAN JAVASCRIPT BIASA --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                
                {{-- Menggunakan onclick native JS agar tidak perlu x-data --}}
                <a href="{{ route('logout') }}" 
                onclick="event.preventDefault(); this.closest('form').submit();"
                class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-700 transition-all duration-200 cursor-pointer">
                    
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    <span>Keluar Aplikasi</span>
                </a>
            </form>
        </div>
    </aside>
</div>