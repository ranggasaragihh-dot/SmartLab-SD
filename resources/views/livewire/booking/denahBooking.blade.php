{{-- PEMBUNGKUS UTAMA GAMBAR (Hanya layout kartu & fungsi zoom) --}}
<div x-data="{ openZoom: false }" class="bg-white p-2 rounded-xl border border-gray-200 shadow-sm h-full flex flex-col">
    
    {{-- 1. GAMBAR THUMBNAIL (Tampilan di Halaman) --}}
    <div 
        class="relative w-full h-full min-h-[200px] rounded-lg overflow-hidden group cursor-zoom-in"
        @click="openZoom = true"
    >
        {{-- Gambar --}}
        <img 
            src="{{ $cluster->denah_url }}" 
            alt="Denah Ruangan" 
            
            class="w-full h-full object-contain">
        >

        {{-- Icon Kaca Pembesar (Muncul saat Hover) --}}
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300 flex items-center justify-center">
            <div class="opacity-0 group-hover:opacity-100 bg-white/90 p-2 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
            </div>
        </div>
    </div>

    {{-- 2. MODAL ZOOM (Muncul saat diklik) --}}
    <div 
        x-show="openZoom" 
        style="display: none;"
        @keydown.escape.window="openZoom = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        {{-- Tombol Close (Pojok Kanan Atas) --}}
        <button 
            @click="openZoom = false" 
            class="absolute top-4 right-4 text-white/70 hover:text-white transition focus:outline-none"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Gambar Full Screen --}}
        <img 
            src="{{ $cluster->denah_url }}" 
            class="max-w-full max-h-screen rounded shadow-2xl object-contain"
            @click.outside="openZoom = false"
        >
        
        {{-- Hint Text (Opsional) --}}
        <p class="absolute bottom-4 text-white/50 text-xs">Tekan ESC atau klik area gelap untuk menutup</p>
    </div>

</div>