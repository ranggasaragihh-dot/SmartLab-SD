<div class="flex h-screen w-full bg-slate-50 overflow-hidden font-sans">

    {{-- 1. SIDEBAR (JANGAN UBAH SESUAI REQUEST) --}}
    <!-- <aside class="w-64 hidden md:block border-r border-gray-200 bg-white z-20"> -->
        @include('livewire.booking.sidebar')
    <!-- </aside> -->

    {{-- 2. KONTEN UTAMA --}}
    <main class="flex-1 flex flex-col min-w-0">
        @include('livewire.booking.header')
        {{-- Header Minimalis --}}
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shadow-sm z-10">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $currentClusterName }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">Silakan pilih unit yang tersedia</p>
            </div>
            
            {{-- Indikator Status (Legend) --}}
            <div class="flex items-center gap-4 text-xs font-medium bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></span>
                    <span class="text-gray-600">Kosong</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                    <span class="text-gray-400">Terisi</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shadow-sm shadow-blue-200"></span>
                    <span class="text-gray-600">Pilihanmu</span>
                </div>
            </div>
        </div>

        {{-- AREA ISI (Grid Split) --}}
        <div class="flex-1 overflow-hidden p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full">

                {{-- KOLOM KIRI: DENAH (Sticky & Visual Reference) --}}
                <div class="lg:col-span-4 flex flex-col">
                    <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-200 w-full h-64 flex flex-col">
                        {{-- Judul Kecil --}}
                        <div class="px-3 py-2 border-b border-gray-100 mb-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Lokasi Ruangan</h3>
                        </div>
                        
                        {{-- Area Gambar --}}
                        <div class="flex-1 relative rounded-xl overflow-hidden bg-gray-50 w-full">
                            @include('livewire.booking.denahBooking')
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: GRID PC (Scrollable) --}}
                <div class="lg:col-span-8 h-full flex flex-col">
                    
                    {{-- Container Putih untuk Grid --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
                        
                        {{-- Header List --}}
                        <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
                            <h3 class="font-bold text-gray-700 text-sm">Daftar Unit PC</h3>
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-md font-bold">
                                {{ $units->count() }} Unit Total
                            </span>
                        </div>

                        {{-- Area Scroll (Daftar PC) --}}
                        <div class="flex-1 overflow-y-auto p-5 custom-scrollbar bg-slate-50/50">
                            {{-- Panggil File Grid --}}
                            @include('livewire.booking.pc-grid-list')
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </main>
    @include('livewire.booking.modals')
</div>