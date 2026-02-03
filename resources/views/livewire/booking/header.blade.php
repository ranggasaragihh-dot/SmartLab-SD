<header class="bg-white border-b border-gray-100 shadow-sm z-10 flex-shrink-0">
    
    {{-- WRAPPER UTAMA: --}}
    {{-- 1. Hapus 'h-20' agar tinggi otomatis menyesuaikan isi (penting saat filter turun ke bawah). --}}
    {{-- 2. Gunakan 'flex-col' untuk mobile (atas-bawah) dan 'md:flex-row' untuk desktop (kiri-kanan). --}}
    <div class="px-4 py-4 md:px-8 md:h-20 flex flex-col md:flex-row md:items-center justify-between gap-4">

        {{-- BAGIAN ATAS: Hamburger + Judul --}}
        {{-- Kita bungkus dalam satu div agar Hamburger & Judul tetap sebaris --}}
        <div class="flex items-center gap-3 w-full md:w-auto">
            
            {{-- Tombol Hamburger --}}
            <button @click="$dispatch('toggle-sidebar')" class="md:hidden text-gray-500 focus:outline-none -ml-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            {{-- Judul Halaman --}}
            <div>
                <h1 class="text-2xl font-bold text-amber-500 leading-none">Booking</h1>
                <p class="text-xs text-gray-400 mt-0.5">Pilih yang ada di bawah.</p>
            </div>
        </div>

        {{-- BAGIAN BAWAH: Filter Area --}}
        {{-- Di Mobile dia akan turun ke baris kedua dan lebar penuh (w-full) --}}
        <div class="w-full md:w-auto">
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">
                
                {{-- 1. FILTER TANGGAL --}}
                <div class="relative w-full">
                    {{-- Ikon Kalender (Hiasan Kiri - Agar Seimbang) --}}
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                         <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>

                    <input type="date" 
                        wire:model.live="filterDate" 
                        class="w-full pl-10 pr-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm uppercase appearance-none"
                        placeholder="Tanggal">
                </div>

                {{-- 2. FILTER STATUS --}}
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>

                    <select wire:model.live="filterStatus" 
                            class="w-full pl-10 pr-8 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-lg focus:ring-blue-500 focus:border-blue-500 block shadow-sm appearance-none truncate">
                        <option value="">Semua</option> 
                        <option value="available">Tersedia</option>
                        <option value="booked">Booked</option>
                    </select>
                </div>
            </div>
        </div>

    </div>
</header>